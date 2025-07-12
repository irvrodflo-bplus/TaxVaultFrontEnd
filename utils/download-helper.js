function downloadFile(blobData, xhr, defaultFilename = "archivo") {
  let filename = defaultFilename;
  const disposition = xhr.getResponseHeader("Content-Disposition");

  if (disposition && disposition.includes("filename=")) {
    filename = decodeURIComponent(
      disposition.split("filename=")[1].split(";")[0].replace(/"/g, "").trim()
    );
  }

  let blob;
  try {
    blob = new Blob([blobData], {
      type: xhr.getResponseHeader("Content-Type") || "application/octet-stream",
    });
  } catch (e) {
    const BlobBuilder =
      window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder;
    if (!BlobBuilder) {
      console.error("Error into download");
      return;
    }

    const blobBuilder = new BlobBuilder();
    blobBuilder.append(blobData);
    blob = blobBuilder.getBlob(
      xhr.getResponseHeader("Content-Type") || "application/octet-stream"
    );
  }

  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.style.display = "none";
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();

  setTimeout(() => {
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  }, 100);
}
