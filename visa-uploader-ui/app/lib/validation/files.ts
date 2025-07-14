import { z } from "zod";

export const FileTypeEnum = z.enum(["passport", "visa", "photo"]);

export const uploadedFileSchema = z.object({
  id: z.number(),
  filename: z.string(),
  preview_url: z.string().url(),
  type: FileTypeEnum,
});

export const fileGroupSchema = z.object({
  passport: z.array(uploadedFileSchema).optional(),
  visa: z.array(uploadedFileSchema).optional(),
  photo: z.array(uploadedFileSchema).optional(),
});
