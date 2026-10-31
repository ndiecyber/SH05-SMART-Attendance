const permissionRequests = Array.isArray(window.smartPermissionData)
  ? window.smartPermissionData
  : [];
const itemsPerPage = 8;
let currentPage = 1;
let visibleRequests = [...permissionRequests];

const badgeStyles = {
  WFH: "bg-sky-100 text-sky-700",
  "Arrive Late": "bg-amber-100 text-amber-700",
  "Business Trip": "bg-indigo-100 text-indigo-700",
  "Leave Early": "bg-orange-100 text-orange-700",
  "Pop Out": "bg-violet-100 text-violet-700",
};

const statusStyles = {
  Approved: "bg-green-100 text-green-700",
  Pending: "bg-yellow-100 text-yellow-700",
  Rejected: "bg-red-100 text-red-700",
  Cancelled: "bg-slate-100 text-slate-600",
};

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => {
    const entities = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };

    return entities[character];
  });
}

function durationMinutesToText(durationMinutes) {
  const hours = Math.floor(durationMinutes / 60);
  const minutes = durationMinutes % 60;

  return `${hours}h ${minutes}m`;
}

function formatDate(date) {
  return new Date(`${date}T00:00:00`).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

function updateClock() {
  const now = new Date();
  const timeElement = document.getElementById("headerTime");
  const dateElement = document.getElementById("headerDate");

  if (timeElement) {
    timeElement.textContent = now.toLocaleTimeString("en-GB", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });
  }

  if (dateElement) {
    dateElement.textContent = now.toLocaleDateString("en-GB", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  }
}

function updateDashboardCards() {
  const activeRequests = permissionRequests.filter(
    (request) => !["Cancelled", "Rejected"].includes(request.status),
  );
  const statusTotals = permissionRequests.reduce(
    (totals, request) => {
      if (request.status === "Pending") totals.pending += 1;
      if (request.status === "Approved") totals.approved += 1;
      if (request.status === "Rejected") totals.rejected += 1;
      return totals;
    },
    { pending: 0, approved: 0, rejected: 0 },
  );
  const totalMinutes = activeRequests.reduce(
    (total, request) => total + request.durationMinutes,
    0,
  );

  document.getElementById("pendingCount").textContent = statusTotals.pending;
  document.getElementById("approvedCount").textContent = statusTotals.approved;
  document.getElementById("rejectedCount").textContent = statusTotals.rejected;
  document.getElementById("totalHoursCount").textContent = durationMinutesToText(totalMinutes);

}

function renderTodayPermissions() {
  const container = document.getElementById("upcomingPermissionList");
  const today = window.smartPermissionToday;
  const todayRequests = permissionRequests.filter(
    (request) => request.date === today && ["Pending", "Approved"].includes(request.status),
  );
  const countElement = document.getElementById("todayPermissionCount");

  countElement.textContent = `${todayRequests.length} Today`;

  if (!todayRequests.length) {
    container.innerHTML = '<div class="py-8 text-center text-sm text-slate-400">No permissions requested for today.</div>';
    return;
  }

  container.innerHTML = todayRequests
    .map((request) => {
      const type = escapeHtml(request.type);
      const status = escapeHtml(request.status);

      return `
        <button type="button" data-permission-id="${request.id}" class="w-full cursor-pointer rounded-2xl border border-slate-200 p-4 text-left transition hover:border-slate-300 hover:bg-slate-50">
          <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${badgeStyles[request.type] || "bg-slate-100"}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <h4 class="truncate font-medium text-slate-800">${type}</h4>
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusStyles[request.status] || "bg-slate-100 text-slate-700"}">${status}</span>
              </div>
              <p class="mt-1 text-xs text-slate-500">${escapeHtml(request.startTime)} - ${escapeHtml(request.endTime)} (${durationMinutesToText(request.durationMinutes)})</p>
              <p class="mt-2 line-clamp-1 text-xs text-slate-500"><span class="font-medium text-slate-600">Reason:</span> ${escapeHtml(request.reason)}</p>
            </div>
          </div>
        </button>
      `;
    })
    .join("");

  container.querySelectorAll("[data-permission-id]").forEach((button) => {
    button.addEventListener("click", () => openModal(Number(button.dataset.permissionId)));
  });
}

function renderHistoryTable() {
  const tbody = document.getElementById("historyTableBody");
  const start = (currentPage - 1) * itemsPerPage;
  const pageRequests = visibleRequests.slice(start, start + itemsPerPage);

  if (!pageRequests.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="py-10 text-center text-sm text-slate-500">No permission requests yet.</td></tr>';
  } else {
    tbody.innerHTML = pageRequests
      .map((request) => `
        <tr class="transition-colors hover:bg-slate-50">
          <td class="px-2 py-3 font-medium text-slate-800">#${request.id}</td>
          <td class="px-2 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold ${badgeStyles[request.type] || "bg-slate-100 text-slate-700"}">${escapeHtml(request.type)}</span></td>
          <td class="px-2 py-3 text-xs sm:text-sm">${formatDate(request.date)}</td>
          <td class="hidden px-2 py-3 text-xs sm:table-cell">${escapeHtml(request.startTime)} - ${escapeHtml(request.endTime)}</td>
          <td class="hidden px-2 py-3 lg:table-cell">${durationMinutesToText(request.durationMinutes)}</td>
          <td class="hidden max-w-[150px] truncate px-2 py-3 md:table-cell">${escapeHtml(request.reason)}</td>
          <td class="px-2 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold ${statusStyles[request.status] || "bg-slate-100 text-slate-700"}">${escapeHtml(request.status)}</span></td>
          <td class="hidden px-2 py-3 text-xs xl:table-cell">${escapeHtml(request.submitted || "-")}</td>
          <td class="px-2 py-3 text-center"><div class="flex justify-center gap-2">
            <button type="button" data-view-id="${request.id}" class="rounded-lg bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-100">View</button>
            ${request.status === "Pending" ? `<button type="button" data-cancel-url="${escapeHtml(request.cancelUrl)}" data-cancel-name="${escapeHtml(request.type)}" class="rounded-lg bg-red-50 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100">Cancel</button>` : ""}
          </div></td>
        </tr>
      `)
      .join("");
  }

  tbody.querySelectorAll("[data-view-id]").forEach((button) => {
    button.addEventListener("click", () => openModal(Number(button.dataset.viewId)));
  });

  tbody.querySelectorAll("[data-cancel-url]").forEach((button) => {
    button.addEventListener("click", () => submitCancellation(button.dataset.cancelUrl, button.dataset.cancelName));
  });

  const totalPages = Math.max(1, Math.ceil(visibleRequests.length / itemsPerPage));
  const firstItem = visibleRequests.length ? start + 1 : 0;
  const lastItem = Math.min(start + itemsPerPage, visibleRequests.length);

  document.getElementById("startItem").textContent = firstItem;
  document.getElementById("endItem").textContent = lastItem;
  document.getElementById("totalItems").textContent = visibleRequests.length;
  document.getElementById("pageIndicator").textContent = `Page ${currentPage} of ${totalPages}`;
  document.getElementById("prevPage").disabled = currentPage <= 1;
  document.getElementById("nextPage").disabled = currentPage >= totalPages;
}

function openModal(id) {
  const request = permissionRequests.find((item) => item.id === id);

  if (!request) return;

  document.getElementById("modalBody").innerHTML = `
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div><p class="mb-1 text-slate-500">Permission ID</p><p class="font-medium text-slate-800">#${request.id}</p></div>
      <div><p class="mb-1 text-slate-500">Status</p><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusStyles[request.status] || "bg-slate-100 text-slate-700"}">${escapeHtml(request.status)}</span></div>
      <div><p class="mb-1 text-slate-500">Type</p><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${badgeStyles[request.type] || "bg-slate-100 text-slate-700"}">${escapeHtml(request.type)}</span></div>
      <div><p class="mb-1 text-slate-500">Date & Time</p><p class="font-medium text-slate-800">${formatDate(request.date)}</p><p class="text-xs text-slate-500">${escapeHtml(request.startTime)} - ${escapeHtml(request.endTime)} (${durationMinutesToText(request.durationMinutes)})</p></div>
      <div class="col-span-2 rounded-xl bg-slate-50 p-3"><p class="mb-1 text-slate-500">Reason</p><p class="font-medium text-slate-800">${escapeHtml(request.reason)}</p></div>
      ${request.reviewNote ? `<div class="col-span-2 rounded-xl bg-indigo-50 p-3"><p class="mb-1 text-indigo-600">Admin Note</p><p class="font-medium text-slate-800">${escapeHtml(request.reviewNote)}</p></div>` : ""}
      <div class="col-span-2 border-t border-slate-100 pt-3"><p class="text-xs text-slate-400">Submitted on: ${escapeHtml(request.submitted || "-")}</p></div>
    </div>
  `;

  const modal = document.getElementById("permissionModal");
  modal.classList.remove("hidden", "opacity-0");
  modal.classList.add("flex");
  document.getElementById("modalContent").classList.remove("scale-95");
}

function closeModal() {
  const modal = document.getElementById("permissionModal");
  modal.classList.add("opacity-0");
  document.getElementById("modalContent").classList.add("scale-95");
  window.setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 300);
}

function submitCancellation(url, permissionType) {
  window.LexaAlerts?.confirm({
    title: "Batalkan Pengajuan Izin?",
    message: `Apakah Anda yakin ingin membatalkan pengajuan ${permissionType}?`,
    detail: "Pengajuan berstatus Pending akan ditandai sebagai dibatalkan.",
    confirmText: "Ya, Batalkan",
    danger: true,
  }).then((accepted) => {
    if (!accepted) return;
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
      window.LexaAlerts?.error("Token keamanan tidak tersedia. Muat ulang halaman lalu coba lagi.");
      return;
    }
    form.innerHTML = `
      <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
      <input type="hidden" name="_method" value="PATCH">
    `;
    document.body.append(form);
    form.submit();
  });
}

function applyFilters() {
  const search = document.getElementById("searchPermission").value.trim().toLowerCase();
  const type = document.getElementById("filterType").value;
  const status = document.getElementById("filterStatus").value;
  const month = document.getElementById("filterMonth").value;

  visibleRequests = permissionRequests.filter((request) => {
    const matchesSearch = !search || `${request.type} ${request.reason}`.toLowerCase().includes(search);
    const matchesType = !type || request.type === type;
    const matchesStatus = !status || request.status.toLowerCase() === status;
    const matchesMonth = !month || Number(request.date.slice(5, 7)) === Number(month);

    return matchesSearch && matchesType && matchesStatus && matchesMonth;
  });

  currentPage = 1;
  renderHistoryTable();
}

function initializePermissionPage() {
  updateClock();
  window.setInterval(updateClock, 1000);
  updateDashboardCards();
  renderTodayPermissions();
  renderHistoryTable();

  ["searchPermission", "filterType", "filterStatus", "filterMonth"].forEach((id) => {
    document.getElementById(id).addEventListener("input", applyFilters);
    document.getElementById(id).addEventListener("change", applyFilters);
  });

  document.getElementById("resetFilter").addEventListener("click", () => {
    document.getElementById("searchPermission").value = "";
    document.getElementById("filterType").value = "";
    document.getElementById("filterStatus").value = "";
    document.getElementById("filterMonth").value = "";
    applyFilters();
  });

  document.getElementById("prevPage").addEventListener("click", () => {
    currentPage = Math.max(1, currentPage - 1);
    renderHistoryTable();
  });

  document.getElementById("nextPage").addEventListener("click", () => {
    currentPage += 1;
    renderHistoryTable();
  });
}

document.addEventListener("DOMContentLoaded", initializePermissionPage);
