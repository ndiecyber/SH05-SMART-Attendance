const headerTime = document.getElementById("headerTime");
const headerDate = document.getElementById("headerDate");

function updateClock() {
  const now = new Date();
  if (headerTime) {
    headerTime.textContent = now.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    });
  }
  if (headerDate) {
    headerDate.textContent = now.toLocaleDateString("en-US", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  }
}

updateClock();
setInterval(updateClock, 1000);

const leaveRequests = Array.isArray(window.smartLeaveRequests) ? window.smartLeaveRequests : [];
const upcomingLeaves = Array.isArray(window.smartUpcomingLeaves) ? window.smartUpcomingLeaves : [];
const dateFormatter = new Intl.DateTimeFormat("en-GB", {
  day: "2-digit",
  month: "short",
  year: "numeric",
});

function parseLocalDate(dateValue) {
  const [year, month, day] = dateValue.split("-").map(Number);
  return new Date(year, month - 1, day);
}

function formatDate(dateValue) {
  return dateFormatter.format(parseLocalDate(dateValue));
}

function renderUpcomingLeaves() {
  const list = document.getElementById("upcomingLeaveList");
  const count = document.getElementById("upcomingLeaveCount");
  if (count) {
    count.textContent = `${upcomingLeaves.length} Leave${upcomingLeaves.length === 1 ? "" : "s"}`;
  }
  if (!list) return;

  list.replaceChildren();
  upcomingLeaves.forEach((leave) => {
    const card = document.createElement("div");
    card.className = "rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50";
    const content = document.createElement("div");
    content.className = "flex items-start justify-between gap-4";
    const details = document.createElement("div");
    const title = document.createElement("h4");
    title.className = "font-medium text-slate-800";
    title.textContent = leave.leave_type;
    const period = document.createElement("p");
    period.className = "mt-1 text-sm text-slate-500";
    period.textContent = `${formatDate(leave.start_date)} – ${formatDate(leave.end_date)}`;
    const duration = document.createElement("span");
    duration.className = "whitespace-nowrap rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700";
    duration.textContent = `${leave.duration} Day${leave.duration === 1 ? "" : "s"}`;
    details.append(title, period);
    content.append(details, duration);
    card.appendChild(content);
    list.appendChild(card);
  });

  if (upcomingLeaves.length === 0) {
    const empty = document.createElement("p");
    empty.className = "rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500";
    empty.textContent = "No approved upcoming leave.";
    list.appendChild(empty);
  }
}

function openLeaveDetails(leave) {
  const modal = document.getElementById("detailModal");
  const body = document.getElementById("modalBody");
  if (!modal || !body) return;

  document.getElementById("modalTitle").textContent = `Leave Request #${leave.id}`;
  body.replaceChildren();
  [
    ["Leave Type", leave.leave_type],
    ["Request Date", formatDate(leave.request_date)],
    ["Period", `${formatDate(leave.start_date)} – ${formatDate(leave.end_date)}`],
    ["Duration", `${leave.duration} Day${leave.duration === 1 ? "" : "s"}`],
    ["Status", leave.status],
    ["Reason", leave.reason],
    ...(leave.review_note ? [["Admin Note", leave.review_note]] : []),
  ].forEach(([label, value]) => {
    const field = document.createElement("div");
    const fieldLabel = document.createElement("p");
    fieldLabel.className = "text-xs font-medium uppercase tracking-wide text-slate-400";
    fieldLabel.textContent = label;
    const fieldValue = document.createElement("p");
    fieldValue.className = "mt-1 whitespace-pre-wrap break-words text-sm text-slate-700";
    fieldValue.textContent = value;
    field.append(fieldLabel, fieldValue);
    body.appendChild(field);
  });

  modal.classList.remove("hidden");
}

function closeLeaveDetails() {
  document.getElementById("detailModal")?.classList.add("hidden");
}

function openCancelLeave(leave) {
  window.LexaAlerts?.confirm({
    title: "Batalkan Pengajuan Cuti?",
    message: `Apakah Anda yakin ingin membatalkan ${leave.leave_type}?`,
    detail: `${formatDate(leave.start_date)} – ${formatDate(leave.end_date)}. Hanya pengajuan Pending yang dapat dibatalkan.`,
    confirmText: "Ya, Batalkan",
    danger: true,
    onConfirm: () => {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = window.leaveCancelUrlTemplate.replace("__LEAVE_REQUEST_ID__", encodeURIComponent(String(leave.id)));
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) throw new Error("Token keamanan tidak tersedia. Muat ulang halaman lalu coba lagi.");
      form.innerHTML = `<input type="hidden" name="_token" value="${escapeHtml(csrfToken)}"><input type="hidden" name="_method" value="PATCH">`;
      document.body.append(form);
      form.submit();
    },
  });
}

document.getElementById("closeModal")?.addEventListener("click", closeLeaveDetails);
document.getElementById("closeModalBtn")?.addEventListener("click", closeLeaveDetails);
document.getElementById("detailModal")?.addEventListener("click", (event) => {
  if (event.target.id === "detailModal") closeLeaveDetails();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeLeaveDetails();
});

function renderLeaveHistory() {
  const searchInput = document.getElementById("searchLeave");
  const typeFilter = document.getElementById("filterType");
  const statusFilter = document.getElementById("filterStatus");
  const yearFilter = document.getElementById("filterYear");
  const resetFilter = document.getElementById("resetFilter");
  const body = document.getElementById("historyTableBody");
  const emptyState = document.getElementById("leaveEmpty");
  const previousButton = document.getElementById("prevPage");
  const nextButton = document.getElementById("nextPage");
  const rowsPerPage = 8;
  let page = 1;
  let filteredRequests = [...leaveRequests];

  (window.smartLeaveYears || []).forEach((year) => {
    const option = document.createElement("option");
    option.value = String(year);
    option.textContent = String(year);
    yearFilter.appendChild(option);
  });

  function render() {
    const query = searchInput.value.trim().toLowerCase();
    const selectedType = typeFilter.value.toLowerCase();
    const selectedStatus = statusFilter.value.toLowerCase();
    const selectedYear = yearFilter.value;
    filteredRequests = leaveRequests.filter((leave) => {
      const matchesSearch = [leave.id, leave.leave_type, leave.start_date, leave.end_date, leave.reason, leave.status]
        .some((value) => String(value).toLowerCase().includes(query));
      const matchesType = !selectedType || leave.leave_type.toLowerCase().includes(selectedType);
      const matchesStatus = !selectedStatus || leave.status.toLowerCase() === selectedStatus;
      const matchesYear = !selectedYear || leave.start_date.startsWith(selectedYear) || leave.request_date.startsWith(selectedYear);
      return matchesSearch && matchesType && matchesStatus && matchesYear;
    });

    const pages = Math.max(1, Math.ceil(filteredRequests.length / rowsPerPage));
    page = Math.min(page, pages);
    const offset = (page - 1) * rowsPerPage;
    const currentRequests = filteredRequests.slice(offset, offset + rowsPerPage);
    body.replaceChildren();

    currentRequests.forEach((leave) => {
      const row = document.createElement("tr");
      row.className = "transition hover:bg-slate-50";
      const values = [
        `LR-${leave.id}`,
        leave.leave_type,
        `${formatDate(leave.start_date)} – ${formatDate(leave.end_date)}`,
        `${leave.duration} Day${leave.duration === 1 ? "" : "s"}`,
        leave.reason,
      ];
      values.forEach((value, index) => {
        const cell = document.createElement("td");
        cell.className = index === 4
          ? "max-w-xs truncate px-4 py-3 text-slate-600"
          : "whitespace-nowrap px-4 py-3 text-slate-600";
        cell.textContent = value;
        row.appendChild(cell);
      });

      const statusCell = document.createElement("td");
      statusCell.className = "whitespace-nowrap px-4 py-3";
      const badge = document.createElement("span");
      const statusClasses = {
        pending: "bg-amber-100 text-amber-700",
        approved: "bg-emerald-100 text-emerald-700",
        rejected: "bg-rose-100 text-rose-700",
        cancelled: "bg-slate-100 text-slate-600",
      };
      badge.className = `inline-flex rounded-full px-3 py-1 text-xs font-medium ${statusClasses[leave.status.toLowerCase()] || "bg-slate-100 text-slate-600"}`;
      badge.textContent = leave.status;
      statusCell.appendChild(badge);
      row.appendChild(statusCell);

      const submittedCell = document.createElement("td");
      submittedCell.className = "whitespace-nowrap px-4 py-3 text-slate-600";
      submittedCell.textContent = formatDate(leave.request_date);
      row.appendChild(submittedCell);

      const actionCell = document.createElement("td");
      actionCell.className = "whitespace-nowrap px-4 py-3 text-center";
      const viewButton = document.createElement("button");
      viewButton.type = "button";
      viewButton.className = "rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50";
      viewButton.textContent = "View";
      viewButton.addEventListener("click", () => openLeaveDetails(leave));
      actionCell.appendChild(viewButton);

      if (leave.status.toLowerCase() === "pending") {
        const cancelButton = document.createElement("button");
        cancelButton.type = "button";
        cancelButton.className = "ml-1 rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50";
        cancelButton.textContent = "Cancel";
        cancelButton.addEventListener("click", () => openCancelLeave(leave));
        actionCell.appendChild(cancelButton);
      }

      row.appendChild(actionCell);
      body.appendChild(row);
    });

    const total = filteredRequests.length;
    const end = Math.min(offset + rowsPerPage, total);
    document.getElementById("startItem").textContent = total === 0 ? "0" : String(offset + 1);
    document.getElementById("endItem").textContent = String(end);
    document.getElementById("totalItems").textContent = String(total);
    document.getElementById("historyTotalBadge").textContent = `${leaveRequests.length} Request${leaveRequests.length === 1 ? "" : "s"}`;
    document.getElementById("pageIndicator").textContent = `Page ${page} of ${pages}`;
    previousButton.disabled = page === 1;
    nextButton.disabled = page === pages || total === 0;
    emptyState.classList.toggle("hidden", total !== 0);
    body.classList.toggle("hidden", total === 0);
  }

  [typeFilter, statusFilter, yearFilter].forEach((control) => control.addEventListener("change", () => { page = 1; render(); }));
  searchInput.addEventListener("input", () => { page = 1; render(); });
  resetFilter.addEventListener("click", () => {
    searchInput.value = "";
    typeFilter.selectedIndex = 0;
    statusFilter.selectedIndex = 0;
    yearFilter.selectedIndex = 0;
    page = 1;
    render();
  });
  previousButton.addEventListener("click", () => { if (page > 1) { page -= 1; render(); } });
  nextButton.addEventListener("click", () => { if (page < Math.ceil(filteredRequests.length / rowsPerPage)) { page += 1; render(); } });
  render();
}

renderUpcomingLeaves();
renderLeaveHistory();
