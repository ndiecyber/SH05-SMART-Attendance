const schedules = Array.isArray(window.smartSchedules) ? window.smartSchedules : [];
const today = window.scheduleToday;
const upcomingSchedules = Array.isArray(window.scheduleUpcoming) ? window.scheduleUpcoming : [];
const scheduleColors = {
  Workshift: "bg-blue-500",
  "Work Shift": "bg-blue-500",
  Meeting: "bg-indigo-500",
  Training: "bg-violet-500",
  Break: "bg-amber-500",
};

function timeToMinutes(time) {
  const [hours, minutes] = time.split(":").map(Number);
  return hours * 60 + minutes;
}

function formatDate(dateString, options = { weekday: "short", day: "numeric", month: "short", year: "numeric" }) {
  const [year, month, day] = dateString.split("-").map(Number);
  return new Intl.DateTimeFormat("en", options).format(new Date(year, month - 1, day));
}

function showScheduleDetails(schedule) {
  const modal = document.getElementById("eventDetailModal");
  if (!modal) return;

  document.getElementById("modalTitle").textContent = schedule.name;
  document.getElementById("modalCategory").textContent = schedule.type;
  document.getElementById("modalDateTime").textContent = `${formatDate(schedule.date)} · ${schedule.start} - ${schedule.end}`;
  document.getElementById("modalLocation").textContent = schedule.location;
  document.getElementById("modalDescription").textContent = schedule.status;
  const badgeIcon = document.getElementById("modalBadgeIcon");
  badgeIcon.textContent = schedule.type.charAt(0);
  badgeIcon.classList.add("text-sm", "font-bold");
  modal.classList.remove("opacity-0", "pointer-events-none");
  document.getElementById("eventModalContent")?.classList.remove("scale-95");
}

function closeScheduleDetails() {
  document.getElementById("eventDetailModal")?.classList.add("opacity-0", "pointer-events-none");
  document.getElementById("eventModalContent")?.classList.add("scale-95");
}

document.getElementById("closeModalBtn")?.addEventListener("click", closeScheduleDetails);
document.getElementById("confirmModalBtn")?.addEventListener("click", closeScheduleDetails);
document.getElementById("eventDetailModal")?.addEventListener("click", (event) => {
  if (event.target.id === "eventDetailModal") closeScheduleDetails();
});

function openDeleteSchedule(schedule, form) {
  form.dataset.confirm = `Hapus jadwal ${schedule.name} secara permanen?`;
  form.dataset.confirmTitle = "Hapus Jadwal?";
  form.dataset.confirmDetail = "Jadwal ini akan dihapus dari daftar dan tidak dapat dipulihkan.";
  form.dataset.confirmTone = "danger";
  form.dataset.confirmText = "Ya, Hapus Jadwal";
  form.requestSubmit();
}

function renderTodayTimeline() {
  const column = document.getElementById("todayOverlapCol");
  if (!column) return;

  const daySchedules = schedules.filter((schedule) => schedule.date === today);
  const startHour = 7;
  const endHour = 19;
  const hourHeight = 48;
  column.style.height = `${(endHour - startHour) * hourHeight}px`;

  if (daySchedules.length === 0) {
    const emptyLabel = document.createElement("p");
    emptyLabel.className = "absolute left-3 top-3 text-xs text-slate-400";
    emptyLabel.textContent = "No schedule today";
    column.appendChild(emptyLabel);
  }

  daySchedules.forEach((schedule) => {
    const start = timeToMinutes(schedule.start);
    const end = timeToMinutes(schedule.end);
    const top = Math.max(0, (start - startHour * 60) * (hourHeight / 60));
    const bottom = Math.min((endHour - startHour) * hourHeight, (end - startHour * 60) * (hourHeight / 60));
    const event = document.createElement("button");
    event.type = "button";
    event.className = `absolute left-2 right-2 overflow-hidden rounded-lg px-3 py-1 text-left text-white shadow-sm ${scheduleColors[schedule.filterType] || "bg-indigo-500"}`;
    event.style.top = `${top}px`;
    event.style.height = `${Math.max(24, bottom - top - 3)}px`;
    event.innerHTML = `<span class="block truncate text-xs font-semibold"></span><span class="block truncate text-[10px] opacity-90"></span>`;
    event.children[0].textContent = schedule.name;
    event.children[1].textContent = `${schedule.start} - ${schedule.end} · ${schedule.location}`;
    event.addEventListener("click", () => showScheduleDetails(schedule));
    column.appendChild(event);
  });
}

function renderUpcomingEvents() {
  const container = document.getElementById("upcomingEventsContainer");
  if (!container) return;

  container.replaceChildren();
  upcomingSchedules.forEach((schedule) => {
    const event = document.createElement("button");
    event.type = "button";
    event.className = "flex w-full items-start gap-3 rounded-2xl border border-slate-100 p-3 text-left transition hover:border-indigo-200 hover:bg-indigo-50/40";
    event.innerHTML = `<span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full ${scheduleColors[schedule.filterType] || "bg-indigo-500"}"></span><span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-slate-700"></span><span class="mt-1 block text-xs text-slate-500"></span><span class="mt-1 block truncate text-xs text-slate-400"></span></span>`;
    event.children[1].children[0].textContent = schedule.name;
    event.children[1].children[1].textContent = `${formatDate(schedule.date)} · ${schedule.start} - ${schedule.end}`;
    event.children[1].children[2].textContent = schedule.location;
    event.addEventListener("click", () => showScheduleDetails(schedule));
    container.appendChild(event);
  });

  if (upcomingSchedules.length === 0) {
    const empty = document.createElement("p");
    empty.className = "rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500";
    empty.textContent = "No upcoming schedules";
    container.appendChild(empty);
  }
}

function renderWeeklySchedule() {
  const initialWeek = new Date(`${window.scheduleWeekStart}T00:00:00`);
  let weekOffset = 0;
  const dayNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

  function render() {
    const weekStart = new Date(initialWeek);
    weekStart.setDate(weekStart.getDate() + weekOffset * 7);
    const rangeEnd = new Date(weekStart);
    rangeEnd.setDate(rangeEnd.getDate() + 6);
    document.getElementById("weekRange").textContent = `${formatDate(toDateString(weekStart), { day: "numeric", month: "short" })} - ${formatDate(toDateString(rangeEnd))}`;

    dayNames.forEach((dayName, index) => {
      const day = new Date(weekStart);
      day.setDate(day.getDate() + index);
      document.getElementById(`dayNum-${index}`).textContent = String(day.getDate());
      const column = document.getElementById(`weeklyCol-${index}`);
      column.replaceChildren();
      column.style.height = "624px";
      const daySchedules = schedules.filter((schedule) => schedule.date === toDateString(day));

      if (daySchedules.length === 0) {
        const emptyLabel = document.createElement("span");
        emptyLabel.className = "pointer-events-none absolute inset-x-0 top-3 text-center text-[9px] text-slate-300";
        emptyLabel.textContent = "No schedule";
        column.appendChild(emptyLabel);
      }

      daySchedules.forEach((schedule) => {
        const start = timeToMinutes(schedule.start);
        const end = timeToMinutes(schedule.end);
        const top = Math.max(0, (start - 7 * 60) * 0.8);
        const bottom = Math.min(624, (end - 7 * 60) * 0.8);
        const event = document.createElement("button");
        event.type = "button";
        event.className = `absolute left-1 right-1 overflow-hidden rounded-md px-1 py-1 text-left text-white shadow-sm ${scheduleColors[schedule.filterType] || "bg-indigo-500"}`;
        event.style.top = `${top}px`;
        event.style.height = `${Math.max(30, bottom - top - 2)}px`;
        event.innerHTML = '<span class="block truncate text-[10px] font-semibold"></span><span class="block truncate text-[9px]"></span>';
        event.children[0].textContent = schedule.name;
        event.children[1].textContent = `${schedule.start} - ${schedule.end}`;
        event.addEventListener("click", () => showScheduleDetails(schedule));
        column.appendChild(event);
      });
    });
  }

  function toDateString(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
  }

  document.getElementById("prevWeek")?.addEventListener("click", () => { weekOffset -= 1; render(); });
  document.getElementById("nextWeek")?.addEventListener("click", () => { weekOffset += 1; render(); });
  document.getElementById("todayWeek")?.addEventListener("click", () => { weekOffset = 0; render(); });
  render();
}

function renderScheduleTable() {
  const body = document.getElementById("scheduleTableBody");
  if (!body) return;

  const typeFilter = document.getElementById("scheduleType");
  const statusFilter = document.getElementById("scheduleStatus");
  const searchInput = document.getElementById("scheduleSearch");
  const previousButton = document.getElementById("schedulePrev");
  const nextButton = document.getElementById("scheduleNext");
  const pageSize = 8;
  let page = 1;
  let filtered = [...schedules];

  function render() {
    const query = searchInput.value.trim().toLowerCase();
    filtered = schedules.filter((schedule) => {
      const searchMatch = [schedule.name, schedule.type, schedule.location, schedule.date].some((value) => value.toLowerCase().includes(query));
      return searchMatch && (typeFilter.value === "all" || schedule.filterType === typeFilter.value) && (statusFilter.value === "all" || schedule.status === statusFilter.value);
    });

    const pageCount = Math.max(1, Math.ceil(filtered.length / pageSize));
    page = Math.min(page, pageCount);
    const offset = (page - 1) * pageSize;
    const visible = filtered.slice(offset, offset + pageSize);
    body.replaceChildren();

    visible.forEach((schedule) => {
      const row = document.createElement("tr");
      row.className = "cursor-pointer transition hover:bg-slate-50";
      row.innerHTML = '<td class="px-6 py-4 text-sm text-slate-600"></td><td class="px-6 py-4 text-sm text-slate-600"></td><td class="px-6 py-4 text-sm font-medium text-slate-800"></td><td class="px-6 py-4 text-sm text-slate-600"></td><td class="px-6 py-4 text-sm text-slate-600"></td><td class="px-6 py-4 text-sm text-slate-600"></td><td class="px-6 py-4 text-sm text-slate-600"></td><td class="whitespace-nowrap px-6 py-4 text-sm"></td>';
      const cells = row.children;
      cells[0].textContent = formatDate(schedule.date);
      cells[1].textContent = schedule.filterType;
      cells[2].textContent = schedule.name;
      cells[3].textContent = `${schedule.start} - ${schedule.end}`;
      cells[4].textContent = `${Math.floor(schedule.durationMinutes / 60)}h ${schedule.durationMinutes % 60}m`;
      cells[5].textContent = schedule.location;
      cells[6].textContent = schedule.status;
      const actions = document.createElement("div");
      actions.className = "flex items-center gap-2";

      if (window.scheduleCanManage) {
      const editLink = document.createElement("a");
      editLink.href = window.scheduleEditUrlTemplate.replace("__SCHEDULE_ID__", encodeURIComponent(String(schedule.id)));
      editLink.className = "inline-flex h-8 w-8 items-center justify-center rounded-lg text-indigo-600 transition hover:bg-indigo-50";
      editLink.title = "Edit schedule";
      editLink.setAttribute("aria-label", `Edit ${schedule.name}`);
      editLink.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>';

      const deleteForm = document.createElement("form");
      deleteForm.action = window.scheduleDeleteUrlTemplate.replace("__SCHEDULE_ID__", encodeURIComponent(String(schedule.id)));
      deleteForm.method = "POST";
      deleteForm.className = "inline-flex";
      const csrfInput = document.createElement("input");
      csrfInput.type = "hidden";
      csrfInput.name = "_token";
      csrfInput.value = window.scheduleCsrfToken;
      const methodInput = document.createElement("input");
      methodInput.type = "hidden";
      methodInput.name = "_method";
      methodInput.value = "DELETE";
      const deleteButton = document.createElement("button");
      deleteButton.type = "button";
      deleteButton.className = "inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-50";
      deleteButton.title = "Delete schedule";
      deleteButton.setAttribute("aria-label", `Delete ${schedule.name}`);
      deleteButton.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-12 0h18" /></svg>';
      deleteButton.addEventListener("click", (event) => {
        event.stopPropagation();
        openDeleteSchedule(schedule, deleteForm);
      });
      deleteForm.append(csrfInput, methodInput, deleteButton);
      actions.append(editLink, deleteForm);
      cells[7].appendChild(actions);
      }

      row.addEventListener("click", (event) => {
        if (!event.target.closest("a, button, form")) showScheduleDetails(schedule);
      });
      body.appendChild(row);
    });

    document.getElementById("scheduleEmpty").classList.toggle("hidden", filtered.length > 0);
    body.classList.toggle("hidden", filtered.length === 0);
    document.getElementById("scheduleStart").textContent = filtered.length === 0 ? "0" : String(offset + 1);
    document.getElementById("scheduleEnd").textContent = String(Math.min(offset + pageSize, filtered.length));
    document.getElementById("scheduleTotal").textContent = String(filtered.length);
    document.getElementById("schedulePage").textContent = String(page);
    previousButton.disabled = page === 1;
    nextButton.disabled = page >= pageCount;
  }

  [typeFilter, statusFilter].forEach((control) => control.addEventListener("change", () => { page = 1; render(); }));
  searchInput.addEventListener("input", () => { page = 1; render(); });
  previousButton.addEventListener("click", () => { if (page > 1) { page -= 1; render(); } });
  nextButton.addEventListener("click", () => { if (page < Math.ceil(filtered.length / pageSize)) { page += 1; render(); } });
  render();
}

renderTodayTimeline();
renderUpcomingEvents();
renderWeeklySchedule();
renderScheduleTable();
