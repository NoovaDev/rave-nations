
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("eventWizard");

    if (!form) return;

    const steps = Array.from(
        form.querySelectorAll(".wizard-step")
    );

    const stepLabels = Array.from(
        document.querySelectorAll("[data-step-label]")
    );

    const progress = document.getElementById("wizardProgress");
    const previousButton = document.getElementById("prevStep");
    const nextButton = document.getElementById("nextStep");
    const saveButton = document.getElementById("saveEvent");
    const stepCounter = document.getElementById("stepCounter");

    const packageRows = document.getElementById("packageRows");
    const addPackageButton = document.getElementById("addPackage");
    const reviewContent = document.getElementById("reviewContent");

    const posterInput = document.getElementById("posterInput");
    const posterFileName = document.getElementById("posterFileName");

    let currentStep = 0;
    let nextPackageIndex = 0;
    let isSubmitting = false;

    const MAX_PACKAGES = 30;
    const MAX_POSTER_SIZE = 3 * 1024 * 1024;

    const escapeHTML = (value) => {
        return String(value ?? "").replace(
            /[&<>"']/g,
            (character) => ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            })[character]
        );
    };

    function getField(name) {
        return form.elements.namedItem(name);
    }

    function showStep(index) {
        currentStep = Math.max(
            0,
            Math.min(index, steps.length - 1)
        );

        steps.forEach((step, i) => {
            step.hidden = i !== currentStep;
        });

        stepLabels.forEach((label, i) => {
            label.classList.toggle(
                "active",
                i === currentStep
            );

            label.classList.toggle(
                "complete",
                i < currentStep
            );

            label.setAttribute(
                "aria-current",
                i === currentStep ? "step" : "false"
            );
        });

        if (progress) {
            progress.style.width =
                `${((currentStep + 1) / steps.length) * 100}%`;
        }

        previousButton.hidden = currentStep === 0;
        nextButton.hidden = currentStep === steps.length - 1;
        saveButton.hidden = currentStep !== steps.length - 1;

        stepCounter.textContent =
            `Step ${currentStep + 1} of ${steps.length}`;

        if (currentStep === steps.length - 1) {
            renderReview();
        }

        const heading = steps[currentStep].querySelector("h2");

        if (heading) {
            heading.setAttribute("tabindex", "-1");
            heading.focus({ preventScroll: true });
        }

        document.querySelector(".wizard-shell")
            ?.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
    }

    function createInput({
        label,
        name,
        type = "text",
        value = "",
        placeholder = "",
        required = false,
        min = null,
        max = null,
        maxlength = null,
        full = false
    }) {
        const wrapper = document.createElement("label");

        if (full) wrapper.classList.add("full");

        const labelText = document.createElement("span");
        labelText.textContent = label;

        wrapper.appendChild(labelText);

        const input = document.createElement("input");

        input.type = type;
        input.name = name;
        input.value = value ?? "";
        input.placeholder = placeholder;
        input.required = required;

        if (min !== null) input.min = min;
        if (max !== null) input.max = max;
        if (maxlength !== null) input.maxLength = maxlength;

        if (type === "number") {
            input.step = name.includes("price") ? "0.01" : "1";
        }

        wrapper.appendChild(input);

        return wrapper;
    }

    function createTextarea(label, name, value = "") {
        const wrapper = document.createElement("label");
        wrapper.className = "full";

        const title = document.createElement("span");
        title.textContent = label;

        const textarea = document.createElement("textarea");

        textarea.name = name;
        textarea.rows = 3;
        textarea.maxLength = 2000;
        textarea.value = value ?? "";

        wrapper.append(title, textarea);

        return wrapper;
    }

    function addPackage(data = {}) {
        if (
            packageRows.querySelectorAll(".package-card").length
            >= MAX_PACKAGES
        ) {
            alert(`Maximum ${MAX_PACKAGES} packages allowed.`);
            return;
        }

        const index = nextPackageIndex++;

        const card = document.createElement("article");
        card.className = "package-card";

        const heading = document.createElement("div");
        heading.className = "package-head";

        const title = document.createElement("h3");
        title.className = "package-title";

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "btn danger remove-package";
        removeButton.textContent = "Remove";

        removeButton.addEventListener("click", () => {
            if (
                packageRows.querySelectorAll(".package-card").length
                <= 1
            ) {
                alert("At least one ticket package is required.");
                return;
            }

            card.remove();
            updatePackageNumbers();
        });

        heading.append(title, removeButton);

        const grid = document.createElement("div");
        grid.className = "form-grid";

        grid.appendChild(createInput({
            label: "Package Name *",
            name: `package_name[${index}]`,
            value: data.name ?? "",
            placeholder: "General / VIP / VVIP",
            required: true,
            maxlength: 100
        }));

        grid.appendChild(createInput({
            label: "Ticket Price (LKR) *",
            name: `package_price[${index}]`,
            type: "number",
            value: data.price ?? "",
            placeholder: "3000.00",
            required: true,
            min: 0,
            max: 99999999.99
        }));

        grid.appendChild(createInput({
            label: "Ticket Limit (Optional)",
            name: `package_limit[${index}]`,
            type: "number",
            value: data.limit ?? "",
            placeholder: "500",
            min: 1,
            max: 10000000
        }));

        grid.appendChild(createTextarea(
            "Short Package Description",
            `package_description[${index}]`,
            data.description ?? ""
        ));

        grid.appendChild(createTextarea(
            "Benefits (One Benefit Per Line)",
            `package_benefits[${index}]`,
            data.benefits ?? ""
        ));

        const activeLabel = document.createElement("label");
        activeLabel.className = "package-active full";

        const activeInput = document.createElement("input");
        activeInput.type = "checkbox";
        activeInput.name = `package_active[${index}]`;
        activeInput.value = "1";

        activeInput.checked =
            data.active === undefined ||
            Number(data.active) === 1;

        const activeText = document.createElement("span");
        activeText.textContent = "Package is available";

        activeLabel.append(activeInput, activeText);
        grid.appendChild(activeLabel);

        card.append(heading, grid);
        packageRows.appendChild(card);

        updatePackageNumbers();
    }

    function updatePackageNumbers() {
        const cards = packageRows.querySelectorAll(".package-card");

        cards.forEach((card, index) => {
            card.querySelector(".package-title").textContent =
                `Package ${index + 1}`;
        });

        addPackageButton.disabled = cards.length >= MAX_PACKAGES;
    }

    function validateStep(stepIndex) {
        const section = steps[stepIndex];

        const fields = section.querySelectorAll(
            "input, select, textarea"
        );

        for (const field of fields) {
            if (field.disabled) continue;

            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }

        if (stepIndex === 0) {
            const file = posterInput?.files?.[0];

            if (file) {
                const allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert("Poster must be JPG, PNG or WEBP.");
                    posterInput.focus();
                    return false;
                }

                if (file.size > MAX_POSTER_SIZE) {
                    alert("Poster cannot exceed 3 MB.");
                    posterInput.focus();
                    return false;
                }
            }
        }

        if (stepIndex === 1) {
            const cards = packageRows.querySelectorAll(
                ".package-card"
            );

            if (!cards.length) {
                alert("Add at least one ticket package.");
                return false;
            }

            for (const card of cards) {
                const benefits = card.querySelector(
                    'textarea[name^="package_benefits["]'
                );

                const items = benefits.value
                    .split(/\r?\n/)
                    .map((item) => item.trim())
                    .filter(Boolean);

                if (
                    items.length > 30 ||
                    items.some((item) => item.length > 200)
                ) {
                    alert(
                        "Maximum 30 benefits, 200 characters each."
                    );
                    benefits.focus();
                    return false;
                }
            }
        }

        return true;
    }

    function renderReview() {
        const value = (name) =>
            getField(name)?.value?.trim() ?? "";

        const cards = Array.from(
            packageRows.querySelectorAll(".package-card")
        );

        const eventDetails = [
            ["Event Name", value("event_name")],
            ["Location", value("location")],
            ["Date", value("event_date")],
            ["Time", value("event_time")],
            ["Status", value("status")],
            ["Description", value("description")]
        ];

        let html = '<div class="review-grid">';

        for (const [label, content] of eventDetails) {
            html += `
                <div class="review-box">
                    <span>${escapeHTML(label)}</span>
                    <strong>${escapeHTML(content || "—")}</strong>
                </div>
            `;
        }

        html += '</div><div class="review-packages">';
        html += "<h3>Ticket Packages</h3>";

        cards.forEach((card, index) => {
            const fieldValue = (prefix) =>
                card.querySelector(
                    `[name^="${prefix}["]`
                )?.value ?? "";

            const name = fieldValue("package_name");
            const price = fieldValue("package_price");
            const description = fieldValue(
                "package_description"
            );

            const benefits = fieldValue(
                "package_benefits"
            );

            const limit = fieldValue("package_limit");

            const active = card.querySelector(
                'input[name^="package_active["]'
            )?.checked;

            html += `
                <article>
                    <strong>
                        ${index + 1}. ${escapeHTML(name)}
                    </strong>
                    <p>
                        LKR ${escapeHTML(price)}
                        ${limit
                            ? ` • Limit: ${escapeHTML(limit)}`
                            : ""}
                    </p>
                    <p>${escapeHTML(description)}</p>
                    <p>
                        Benefits:
                        ${escapeHTML(benefits || "None")
                            .replace(/\r?\n/g, "<br>")}
                    </p>
                    <p>${active ? "Available" : "Inactive"}</p>
                </article>
            `;
        });

        html += "</div>";

        reviewContent.innerHTML = html;
    }

    nextButton.addEventListener("click", () => {
        if (!validateStep(currentStep)) return;

        showStep(currentStep + 1);
    });

    previousButton.addEventListener("click", () => {
        showStep(currentStep - 1);
    });

    addPackageButton.addEventListener("click", () => {
        addPackage();
    });

    posterInput?.addEventListener("change", () => {
        const file = posterInput.files?.[0];

        posterFileName.textContent = file
            ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`
            : "No new poster selected.";
    });

    form.addEventListener("submit", (event) => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!validateStep(0)) {
            event.preventDefault();
            showStep(0);
            return;
        }

        if (!validateStep(1)) {
            event.preventDefault();
            showStep(1);
            return;
        }

        isSubmitting = true;
        saveButton.disabled = true;
        saveButton.textContent = "Saving Event...";
    });

    // Load packages from PHP when editing an event.
    const initialData = document.getElementById(
        "initialPackages"
    );

    let existingPackages = [];

    if (initialData) {
        try {
            const parsed = JSON.parse(initialData.textContent);

            if (Array.isArray(parsed)) {
                existingPackages = parsed;
            }
        } catch (error) {
            console.error("Invalid initial package data.", error);
        }
    }

    if (existingPackages.length) {
        existingPackages.forEach(addPackage);
    } else {
        addPackage();
    }

    showStep(0);
});
