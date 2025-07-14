import { ChangeEvent, useRef } from "react";
import { Plus } from "lucide-react";
import { toast } from "react-toastify";
import { uploadFile } from "../lib/api/files";
import { FileType } from "../types/files";

interface FileUploadButtonProps {
  type: FileType;
  onUpload: () => void;
}

export function FileUploadButton({ type, onUpload }: FileUploadButtonProps) {
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      await uploadFile(file, type);
      toast.success("File uploaded successfully!");
      onUpload();
    } catch {
      toast.error("Failed to upload file.");
    }
  };

  return (
    <>
      <input
        ref={inputRef}
        type="file"
        className="hidden"
        onChange={handleFileChange}
      />
      <button
        type="button"
        onClick={() => inputRef.current?.click()}
        className="flex items-center justify-center h-24 w-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
      >
        <Plus className="text-blue-500" />
      </button>
    </>
  );
}
