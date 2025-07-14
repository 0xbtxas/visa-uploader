export type FileType = "passport" | "visa" | "photo";

export interface UploadedFile {
  id: number;
  filename: string;
  preview_url: string;
  type: FileType;
}
