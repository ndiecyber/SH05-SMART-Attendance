const attendanceImportModal = document.getElementById("importCsvModal");
const attendanceCsvInput = document.getElementById("attendanceCsvFile");
const attendanceCsvDropzone = document.getElementById("csvDropzone");
const attendanceCsvFileName = document.getElementById("csvFileName");
const attendanceCsvFileSize = document.getElementById("csvFileSize");
const attendanceCsvImportError = document.getElementById("csvImportError");
const attendanceCsvSubmit = document.getElementById("submitCsvImport");
const attendanceCsvPreview = document.getElementById("csvPreview");
let attendanceImportScrollState = null;

if (attendanceImportModal && attendanceImportModal.parentElement !== document.body) {
  document.body.append(attendanceImportModal);
}

function openAttendanceImportModal() {
  if (!attendanceImportModal || !attendanceImportModal.classList.contains("hidden")) return;

  const root = document.documentElement;
  const body = document.body;
  const scrollY = window.scrollY;

  attendanceImportScrollState = {
    scrollY,
    rootOverflowY: root.style.overflowY,
    bodyPosition: body.style.position,
    bodyTop: body.style.top,
    bodyLeft: body.style.left,
    bodyRight: body.style.right,
    bodyWidth: body.style.width,
  };

  root.style.overflowY = "scroll";
  body.style.position = "fixed";
  body.style.top = `-${scrollY}px`;
  body.style.left = "0";
  body.style.right = "0";
  body.style.width = "auto";
  attendanceImportModal.classList.remove("hidden");
  attendanceImportModal.classList.add("flex");
}

function closeAttendanceImportModal() {
  if (!attendanceImportModal || attendanceImportModal.classList.contains("hidden")) return;

  attendanceImportModal.classList.add("hidden");
  attendanceImportModal.classList.remove("flex");

  if (attendanceImportScrollState) {
    const root = document.documentElement;
    const body = document.body;
    const { scrollY, rootOverflowY, bodyPosition, bodyTop, bodyLeft, bodyRight, bodyWidth } = attendanceImportScrollState;

    body.style.position = bodyPosition;
    body.style.top = bodyTop;
    body.style.left = bodyLeft;
    body.style.right = bodyRight;
    body.style.width = bodyWidth;
    root.style.overflowY = rootOverflowY;
    attendanceImportScrollState = null;
    window.scrollTo(0, scrollY);
  }
}

document.getElementById("openImportModal")?.addEventListener("click", openAttendanceImportModal);
document.getElementById("closeImportModal")?.addEventListener("click", closeAttendanceImportModal);
document.getElementById("cancelImportModal")?.addEventListener("click", closeAttendanceImportModal);
attendanceImportModal?.addEventListener("click", (event) => {
  if (event.target === attendanceImportModal) closeAttendanceImportModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && attendanceImportModal && !attendanceImportModal.classList.contains("hidden")) {
    closeAttendanceImportModal();
  }
});

function parseAttendanceCsv(text) {
  const rows = [];
  let row = [];
  let field = "";
  let quoted = false;

  for (let index = 0; index < text.length; index += 1) {
    const character = text[index];

    if (character === '"') {
      if (quoted && text[index + 1] === '"') {
        field += '"';
        index += 1;
      } else {
        quoted = !quoted;
      }
    } else if (character === "," && !quoted) {
      row.push(field);
      field = "";
    } else if ((character === "\n" || character === "\r") && !quoted) {
      if (character === "\r" && text[index + 1] === "\n") index += 1;
      row.push(field);
      rows.push(row);
      row = [];
      field = "";
    } else {
      field += character;
    }
  }

  if (field !== "" || row.length) {
    row.push(field);
    rows.push(row);
  }

  return rows.filter((values) => values.some((value) => value.trim() !== ""));
}

function renderAttendanceCsvPreview(rows) {
  const [header = [], ...dataRows] = rows;
  const normalizedHeader = header.map((value, index) => (index === 0 ? value.replace(/^\uFEFF/, "") : value).trim().toLowerCase());
  const expectedHeader = ["attendance_date", "check_in_at", "check_out_at"];
  const headerIsValid = normalizedHeader.length === expectedHeader.length
    && expectedHeader.every((column) => normalizedHeader.includes(column));

  if (!headerIsValid) {
    if (attendanceCsvImportError) {
      attendanceCsvImportError.textContent = "CSV header harus berisi attendance_date, check_in_at, check_out_at.";
      attendanceCsvImportError.classList.remove("hidden");
    }
    attendanceCsvSubmit?.setAttribute("disabled", "disabled");
    attendanceCsvPreview?.classList.add("hidden");
    return;
  }

  attendanceCsvImportError?.classList.add("hidden");
  attendanceCsvSubmit?.removeAttribute("disabled");
  attendanceCsvPreview?.classList.remove("hidden");
  document.getElementById("csvPreviewSummary").textContent = `${dataRows.length} data row${dataRows.length === 1 ? "" : "s"} found. Previewing up to 5.`;

  const headerContainer = document.getElementById("csvPreviewHeader");
  const rowContainer = document.getElementById("csvPreviewRows");
  headerContainer.replaceChildren();
  rowContainer.replaceChildren();

  const headingRow = document.createElement("tr");
  normalizedHeader.forEach((column) => {
    const cell = document.createElement("th");
    cell.className = "whitespace-nowrap px-2 py-2 font-semibold";
    cell.textContent = column;
    headingRow.append(cell);
  });
  headerContainer.append(headingRow);

  dataRows.slice(0, 5).forEach((values) => {
    const tableRow = document.createElement("tr");
    normalizedHeader.forEach((_, index) => {
      const cell = document.createElement("td");
      cell.className = "whitespace-nowrap px-2 py-2";
      cell.textContent = values[index] || "";
      tableRow.append(cell);
    });
    rowContainer.append(tableRow);
  });
}

async function displayAttendanceCsvFile(file) {
  const isCsv = file && file.name.toLowerCase().endsWith(".csv");
  attendanceCsvImportError?.classList.toggle("hidden", isCsv);
  if (!isCsv) {
    if (attendanceCsvImportError) attendanceCsvImportError.textContent = "Pilih file dengan ekstensi .csv.";
    attendanceCsvImportError?.classList.remove("hidden");
    if (attendanceCsvInput) attendanceCsvInput.value = "";
    attendanceCsvSubmit?.setAttribute("disabled", "disabled");
    attendanceCsvPreview?.classList.add("hidden");
    return;
  }

  if (attendanceCsvFileName) {
    attendanceCsvFileName.textContent = file.name;
    attendanceCsvFileName.classList.remove("hidden");
  }
  if (attendanceCsvFileSize) {
    attendanceCsvFileSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;
    attendanceCsvFileSize.classList.remove("hidden");
  }
  attendanceCsvSubmit?.setAttribute("disabled", "disabled");

  try {
    renderAttendanceCsvPreview(parseAttendanceCsv(await file.text()));
  } catch {
    if (attendanceCsvImportError) {
      attendanceCsvImportError.textContent = "File CSV tidak dapat dibaca untuk preview.";
      attendanceCsvImportError.classList.remove("hidden");
    }
    attendanceCsvPreview?.classList.add("hidden");
    attendanceCsvSubmit?.setAttribute("disabled", "disabled");
  }
}

attendanceCsvInput?.addEventListener("change", () => displayAttendanceCsvFile(attendanceCsvInput.files?.[0]));
attendanceCsvDropzone?.addEventListener("dragover", (event) => {
  event.preventDefault();
  attendanceCsvDropzone.classList.add("border-indigo-500", "bg-indigo-50");
});
attendanceCsvDropzone?.addEventListener("dragleave", () => {
  attendanceCsvDropzone.classList.remove("border-indigo-500", "bg-indigo-50");
});
attendanceCsvDropzone?.addEventListener("drop", (event) => {
  event.preventDefault();
  attendanceCsvDropzone.classList.remove("border-indigo-500", "bg-indigo-50");
  const file = event.dataTransfer?.files?.[0];
  if (!file || !attendanceCsvInput) return;
  const transfer = new DataTransfer();
  transfer.items.add(file);
  attendanceCsvInput.files = transfer.files;
  displayAttendanceCsvFile(file);
});
