function setError(errorElement, message) {
    errorElement.textContent = message;
}

const CONTACT_DRAFT_KEY = "schoen:contact:draft";

function saveDraft(fields) {
    const draft = {
        name: fields.nameInput.value,
        email: fields.emailInput.value,
        message: fields.messageInput.value
    };
    sessionStorage.setItem(CONTACT_DRAFT_KEY, JSON.stringify(draft));
}

function restoreDraft(fields) {
    const raw = sessionStorage.getItem(CONTACT_DRAFT_KEY);
    if (!raw) return;
    try {
        const draft = JSON.parse(raw);
        fields.nameInput.value = draft.name || "";
        fields.emailInput.value = draft.email || "";
        fields.messageInput.value = draft.message || "";
    } catch (_) {
        sessionStorage.removeItem(CONTACT_DRAFT_KEY);
    }
}

function clearDraft() {
    sessionStorage.removeItem(CONTACT_DRAFT_KEY);
}

function setStatus(statusElement, message, type = "") {
    if (!statusElement) return;
    statusElement.textContent = message;
    statusElement.classList.remove("is-success", "is-error");
    if (type) {
        statusElement.classList.add(type);
    }
}

function clearErrors(errorElements) {
    errorElements.forEach((element) => {
        element.textContent = "";
    });
}

function validateContactForm(fields) {
    const {
        nameInput,
        emailInput,
        messageInput,
        nameError,
        emailError,
        messageError
    } = fields;

    let hasError = false;
    clearErrors([nameError, emailError, messageError]);

    if (!nameInput.value.trim()) {
        setError(nameError, "お名前を入力してください。");
        hasError = true;
    }

    if (!emailInput.value.trim()) {
        setError(emailError, "メールアドレスを入力してください。");
        hasError = true;
    } else if (!emailInput.checkValidity()) {
        setError(emailError, "メールアドレスの形式が正しくありません。");
        hasError = true;
    }

    if (!messageInput.value.trim()) {
        setError(messageError, "メッセージを入力してください。");
        hasError = true;
    }

    return !hasError;
}

function initContactForm() {
    const form = document.querySelector(".contact-form");
    const submitButton = document.querySelector(".contact-form-button");
    const statusElement = document.querySelector("#contactStatus");

    if (!form || !submitButton) return false;
    if (form.dataset.contactInit === "1") return true;

    const fields = {
        nameInput: form.querySelector("#name"),
        emailInput: form.querySelector("#email"),
        messageInput: form.querySelector("#message"),
        nameError: form.querySelector("#nameError"),
        emailError: form.querySelector("#emailError"),
        messageError: form.querySelector("#messageError")
    };

    const hasAllFields = Object.values(fields).every(Boolean);
    if (!hasAllFields) return false;
    restoreDraft(fields);

    [fields.nameInput, fields.emailInput, fields.messageInput].forEach((input) => {
        input.addEventListener("input", () => saveDraft(fields));
    });

    const handleSubmit = async (event) => {
        event.preventDefault();
        setStatus(statusElement, "");
        const isValid = validateContactForm(fields);
        if (!isValid) {
            saveDraft(fields);
            setStatus(statusElement, "入力内容を確認してください。", "is-error");
            return;
        }

        submitButton.disabled = true;
        setStatus(statusElement, "送信中です...", "");

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: {
                    Accept: "application/json"
                }
            });
            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (data.fieldErrors && typeof data.fieldErrors === "object") {
                    setError(fields.nameError, data.fieldErrors.name || "");
                    setError(fields.emailError, data.fieldErrors.email || "");
                    setError(fields.messageError, data.fieldErrors.message || "");
                    setStatus(statusElement, "入力内容を確認してください。", "is-error");
                    return;
                }
                setStatus(statusElement, data.message || "送信に失敗しました。", "is-error");
                return;
            }

            setStatus(statusElement, data.message || "送信が完了しました。", "is-success");
            form.reset();
            clearErrors([fields.nameError, fields.emailError, fields.messageError]);
            clearDraft();
        } catch (error) {
            saveDraft(fields);
            setStatus(statusElement, "通信に失敗しました。時間をおいて再度お試しください。", "is-error");
        } finally {
            submitButton.disabled = false;
        }
    };

    form.addEventListener("submit", handleSubmit);
    form.dataset.contactInit = "1";
    return true;
}

function tryInitContactForm() {
    if (initContactForm()) return;
    document.addEventListener("schoen:page-includes-done", () => initContactForm(), { once: true });
}

document.addEventListener("DOMContentLoaded", tryInitContactForm);
