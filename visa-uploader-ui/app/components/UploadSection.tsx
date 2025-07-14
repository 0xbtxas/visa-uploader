import { Card, CardContent } from "./ui/card";
import { Trash } from "lucide-react";
import { FileUploadButton } from "./FileUploadButton";
import { FileType, UploadedFile } from "../types/files";

interface UploadSectionProps {
  title: string;
  files: UploadedFile[];
  fileType: FileType;
  onUpload: () => void;
  onDelete: (id: number) => void;
}

export function UploadSection({
  title,
  files,
  fileType,
  onUpload,
  onDelete,
}: UploadSectionProps) {
  return (
    <Card className="flex-1 min-w-[300px] p-4">
      <CardContent>
        <h3 className="text-lg font-semibold mb-2">{title}</h3>

        <div className="space-y-2">
          <FileUploadButton type={fileType} onUpload={onUpload} />

          <p className="text-xs text-green-600 mt-2">
            <span className="font-medium">Click to upload</span> <br />
            <span className="text-gray-500">PDF, JPG, PNG (max. 4MB)</span>
          </p>

          {files?.map((file) => (
            <div
              key={file.id}
              className="flex items-center justify-between bg-gray-100 rounded px-2 py-1"
            >
              <div className="flex items-center gap-2">
                <img
                  src={file.preview_url}
                  alt={file.filename}
                  className="h-8 w-8 object-cover rounded"
                />
                <span className="text-sm">{file.filename}</span>
              </div>
              <button
                onClick={() => onDelete(file.id)}
                className="text-red-500 text-xs hover:underline"
              >
                <Trash />
              </button>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
