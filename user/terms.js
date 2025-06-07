document.addEventListener('DOMContentLoaded', function () {
    const termsModal = document.getElementById('termsModal');
    const termsContent = document.getElementById('termsContent');
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const acceptBtn = document.getElementById('acceptBtn');

    if (termsModal) {
        termsContent.addEventListener('scroll', function () {
            if (termsContent.scrollTop + termsContent.clientHeight >= termsContent.scrollHeight) {
                agreeCheckbox.disabled = false;
            }
        });

        agreeCheckbox.addEventListener('change', function () {
            acceptBtn.disabled = !agreeCheckbox.checked;
        });

        acceptBtn.addEventListener('click', function () {
            termsModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    }
});
