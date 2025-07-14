import { api } from "../axios";
import { FileType, UploadedFile } from "../../types/files";
import { fileGroupSchema } from "../validation/files";

/**
 * Uploads a file with a given type.
 */
export async function uploadFile(file: File, type: FileType) {
  const formData = new FormData();
  formData.append("file", file);
  formData.append("type", type);

  const response = await api.post("/files", formData);
  return response.data;
}

/**
 * Gets all uploaded files grouped by type.
 */
export async function getFilesGrouped(): Promise<
  Record<FileType, UploadedFile[] | undefined>
> {
  const response = await api.get("/files");
  const parsed = fileGroupSchema.parse(response.data);

  const result: Record<FileType, UploadedFile[] | undefined> = {
    passport: parsed.passport ?? [],
    visa: parsed.visa ?? [],
    photo: parsed.photo ?? [],
  };

  return result;
}

/**
 * Deletes a file by its ID.
 */
export async function deleteFile(id: number): Promise<void> {
  await api.delete(`/files/${id}`);
}
