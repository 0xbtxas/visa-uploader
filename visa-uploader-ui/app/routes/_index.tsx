import { useEffect, useState } from "react";
import { ToastContainer, toast } from "react-toastify";
import { UploadSection } from "../components/UploadSection";
import { getFilesGrouped, deleteFile } from "../lib/api/files";
import { FileType, UploadedFile } from "../types/files";
import "react-toastify/dist/ReactToastify.css";

export default function VisaUploader() {
  const [filesByType, setFilesByType] = useState<
    Record<FileType, UploadedFile[]>
  >({
    passport: [],
    visa: [],
    photo: [],
  });

  const fetchAllFiles = async () => {
    try {
      const data = await getFilesGrouped();
      setFilesByType({
        passport: data.passport ?? [],
        visa: data.visa ?? [],
        photo: data.photo ?? [],
      });
    } catch {
      toast.error("Failed to fetch files.");
    }
  };

  const handleDelete = async (id: number) => {
    try {
      await deleteFile(id);
      toast.success("File deleted.");
      fetchAllFiles();
    } catch {
      toast.error("Failed to delete file.");
    }
  };

  useEffect(() => {
    fetchAllFiles();
  }, []);

  const totalUploaded =
    (filesByType?.passport?.length ?? 0) +
    (filesByType?.visa?.length ?? 0) +
    (filesByType?.photo?.length ?? 0);

  const fileSections: { label: string; type: FileType }[] = [
    { label: "Passport", type: "passport" },
    { label: "Visa", type: "visa" },
    { label: "Photo", type: "photo" },
  ];
  return (
    <>
      <ToastContainer position="top-right" autoClose={3000} />
      <div className="p-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-bold">
            Essential documents to be reviewed
          </h2>
          <span className="text-blue-600 text-sm font-medium">
            {totalUploaded} files uploaded
          </span>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {fileSections.map(({ label, type }) => (
            <UploadSection
              key={type}
              title={label}
              files={filesByType[type]}
              fileType={type}
              onUpload={fetchAllFiles}
              onDelete={handleDelete}
            />
          ))}
        </div>
      </div>
    </>
  );
}
