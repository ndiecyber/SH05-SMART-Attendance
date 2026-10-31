 document.addEventListener("DOMContentLoaded", () => {
  const forms = [...document.querySelectorAll("form[data-settings-form]")];
  const status = document.getElementById("settingsSaveStatus");
  const saveButton = document.getElementById("settingsSaveButton");
  const tabs = [...document.querySelectorAll("[data-settings-tab]")];
  const panels = [...document.querySelectorAll("[data-settings-panel]")];
  const validTabs = new Set(tabs.map((tab) => tab.dataset.settingsTab));
  let activeTab = "profile";

  const setStatus = (label, tone = "saved") => {
    if (!status) return;
    status.className = `inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full px-3 py-2 text-xs font-semibold ${tone === "error" ? "bg-rose-50 text-rose-700" : tone === "pending" ? "bg-amber-50 text-amber-800" : "bg-emerald-50 text-emerald-700"}`;
    const indicator = document.createElement("span");
    indicator.className = `h-2 w-2 rounded-full ${tone === "error" ? "bg-rose-500" : tone === "pending" ? "bg-amber-500" : "bg-emerald-500"}`;
    status.replaceChildren(indicator, document.createTextNode(label));
  };

  const activeForm = () => forms.find((form) => form.dataset.settingsForm === activeTab);
  const updateSaveButton = () => {
    if (saveButton) saveButton.disabled = !activeForm();
  };
  const activateTab = (tabName) => {
    if (!validTabs.has(tabName)) return;
    activeTab = tabName;
    tabs.forEach((tab) => {
      const isActive = tab.dataset.settingsTab === tabName;
      tab.setAttribute("aria-selected", String(isActive));
      tab.classList.toggle("bg-white", isActive);
      tab.classList.toggle("text-slate-900", isActive);
      tab.classList.toggle("shadow-sm", isActive);
      tab.classList.toggle("text-slate-600", !isActive);
    });
    panels.forEach((panel) => panel.classList.toggle("hidden", panel.dataset.settingsPanel !== tabName));
    history.replaceState(null, "", `#${tabName}`);
    updateSaveButton();
  };

  tabs.forEach((tab) => tab.addEventListener("click", () => activateTab(tab.dataset.settingsTab)));
  document.querySelectorAll("[data-settings-open-tab]").forEach((link) => {
    link.addEventListener("click", () => activateTab(link.dataset.settingsOpenTab));
  });
  activateTab(new URLSearchParams(location.search).get("tab") || location.hash.slice(1) || "profile");

  forms.forEach((form) => {
    form.querySelectorAll("input, select, textarea").forEach((field) => {
      const markDirty = () => {
        form.dataset.dirty = "true";
        if (field.matches('[name="current_password"], [name="new_password"], [name="new_password_confirmation"]')) {
          form.dataset.passwordTouched = "true";
        }
        setStatus("Perubahan belum disimpan", "pending");
      };
      field.addEventListener("input", markDirty);
      field.addEventListener("change", markDirty);
    });
    form.addEventListener("submit", () => {
      setStatus("Menyimpan perubahan…", "pending");
      if (saveButton) {
        saveButton.disabled = true;
        saveButton.classList.add("opacity-70", "cursor-wait");
      }
    });
  });

  saveButton?.addEventListener("click", () => {
    const form = activeForm();
    if (!form) return;
    if (form.dataset.settingsForm === "security" && form.dataset.passwordTouched !== "true") {
      setStatus("Tidak ada perubahan kata sandi");
      return;
    }
    const otherDirtyForm = forms.find((candidate) => candidate !== form && candidate.dataset.dirty === "true");
    if (otherDirtyForm) {
      setStatus("Simpan perubahan pada tab lainnya terlebih dahulu", "error");
      return;
    }
    form.requestSubmit();
  });

  document.querySelectorAll("[data-password-toggle]").forEach((button) => {
    button.addEventListener("click", () => {
      const field = button.closest(".settings-password-field")?.querySelector("[data-password-field]");
      if (!field) return;
      field.type = field.type === "password" ? "text" : "password";
      button.textContent = field.type === "password" ? "Lihat" : "Sembunyikan";
    });
  });

  const profileForm = forms.find((form) => form.dataset.settingsForm === "profile");
  const bioField = profileForm?.querySelector('[name="bio"]');
  const bioCount = document.querySelector("[data-bio-count]");
  const updateBioCount = () => {
    if (bioField && bioCount) bioCount.textContent = `${bioField.value.length}/240 karakter`;
  };
  bioField?.addEventListener("input", updateBioCount);
  updateBioCount();

  const avatarInput = document.getElementById("profilePhotoInput");
  const avatarPreview = document.getElementById("profilePhotoPreview");
  const avatarError = document.querySelector("[data-avatar-error]");
  avatarInput?.addEventListener("change", () => {
    const file = avatarInput.files?.[0];
    if (!file) return;
    if (!new Set(["image/jpeg", "image/png", "image/webp"]).has(file.type) || file.size > 2 * 1024 * 1024) {
      avatarInput.value = "";
      avatarError?.classList.remove("hidden");
      return;
    }
    avatarError?.classList.add("hidden");
    if (avatarPreview) avatarPreview.src = URL.createObjectURL(file);
    profileForm.dataset.dirty = "true";
    setStatus("Perubahan belum disimpan", "pending");
  });
});
