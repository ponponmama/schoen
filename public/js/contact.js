function setError(errorElement, message) {
    errorElement.textContent = message;
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

    const handleSubmit = (event) => {
        event.preventDefault();
        const isValid = validateContactForm(fields);
        if (isValid) {
            form.submit();
        }
    };

    form.addEventListener("submit", handleSubmit);
    submitButton.addEventListener("click", handleSubmit);
    form.dataset.contactInit = "1";
    return true;
}

function tryInitContactForm() {
    if (initContactForm()) return;
    document.addEventListener("schoen:page-includes-done", () => initContactForm(), { once: true });
}

document.addEventListener("DOMContentLoaded", tryInitContactForm);
