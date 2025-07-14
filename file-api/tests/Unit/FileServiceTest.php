<?php

namespace Tests\Unit;

use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->fileService = new FileService();
    }

    public function test_it_stores_uploaded_file_metadata()
    {
        $file = HttpUploadedFile::fake()->image('test.jpg', 600, 600);

        $stored = $this->fileService->store($file, 'passport');

        Storage::disk('local')->assertExists($stored->path);
        $this->assertDatabaseHas('uploaded_files', [
            'filename' => 'test.jpg',
            'type' => 'passport',
        ]);
    }

    public function test_it_deletes_file_and_database_record()
    {
        $file = HttpUploadedFile::fake()->image('delete_me.jpg');
        $stored = $this->fileService->store($file, 'passport');

        $this->fileService->delete($stored);

        Storage::disk('local')->assertMissing($stored->path);
        $this->assertDatabaseMissing('uploaded_files', ['id' => $stored->id]);
    }

    public function test_it_generates_thumbnail_for_images()
    {
        $file = HttpUploadedFile::fake()->image('sample.jpg', 800, 800);
        $uploaded = $this->fileService->store($file, 'passport');

        Storage::disk('local')->put($uploaded->path, file_get_contents($file->getPathname()));

        $thumbnailPath = $this->fileService->generateThumbnail($uploaded);

        $this->assertNotNull($thumbnailPath);
    }
    public function test_it_generates_pdf_thumbnail_as_fallback()
    {
        // Simulate fallback image
        Storage::disk('local')->put('fallbacks/pdf.png', 'fake-content');

        $file = HttpUploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');
        $uploaded = $this->fileService->store($file, 'passport');

        $result = $this->fileService->generateThumbnail($uploaded);

        Storage::disk('local')->assertExists("thumbnails/{$uploaded->id}.jpg");
        $this->assertNotNull($result);
    }

    public function test_it_returns_correct_thumbnail_path()
    {
        $file = HttpUploadedFile::fake()->create('sample.doc', 10);
        $uploaded = $this->fileService->store($file, 'passport');

        $path = $this->fileService->getThumbnailPath($uploaded);

        $this->assertEquals("thumbnails/{$uploaded->id}.jpg", $path);
    }
}
