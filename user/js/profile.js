document.addEventListener('DOMContentLoaded', () => {
  const profileBtn = document.getElementById('profile-button');
  const passwordBtn = document.getElementById('password-button');
  const profileForm = document.getElementById('profile-form');
  const passwordForm = document.getElementById('password-form');

  profileForm.classList.remove('hidden');
  passwordForm.classList.add('hidden');
  profileBtn.classList.add('active-btn');

  profileBtn.addEventListener('click', () => {
    profileForm.classList.remove('hidden');
    passwordForm.classList.add('hidden');
    profileBtn.classList.add('active-btn');
    passwordBtn.classList.remove('active-btn');
  });

  passwordBtn.addEventListener('click', () => {
    passwordForm.classList.remove('hidden');
    profileForm.classList.add('hidden');
    passwordBtn.classList.add('active-btn');
    profileBtn.classList.remove('active-btn');
  });
});
document.querySelectorAll('.success-msg').forEach(msg => {
  setTimeout(() => msg.style.display = 'none', 10000);
});

function showRules() {
  const rules = document.getElementById('password-rules');
  if (rules.classList.contains('hidden')) {
    rules.classList.remove('hidden');
  }
}

function checkPasswordStrength() {
  const password = document.getElementById('new-password').value;

  const lengthRule = document.getElementById('length-rule');
  const upperRule = document.getElementById('uppercase-rule');
  const lowerRule = document.getElementById('lowercase-rule');
  const numberRule = document.getElementById('number-rule');
  const specialRule = document.getElementById('special-rule');

  updateRule(lengthRule, password.length >= 8);
  updateRule(upperRule, /[A-Z]/.test(password));
  updateRule(lowerRule, /[a-z]/.test(password));
  updateRule(numberRule, /\d/.test(password));
  updateRule(specialRule, /[\W_]/.test(password));
}

function updateRule(element, isValid) {
  element.classList.remove('text-gray-600', 'text-green-600', 'text-red-500');
  const icon = element.querySelector('i');
  icon.classList.remove('fa-circle', 'fa-check', 'fa-times');

  if (isValid) {
    element.classList.add('text-green-600');
    icon.classList.add('fa-check');
  } else {
    element.classList.add('text-red-500');
    icon.classList.add('fa-times');
  }
}

function validatePasswordForm() {
  const password = document.getElementById('new-password').value;
  const confirmPassword = document.getElementById('confirm-password').value;

  const isValid = (
    password.length >= 8 &&
    /[A-Z]/.test(password) &&
    /[a-z]/.test(password) &&
    /\d/.test(password) &&
    /[\W_]/.test(password)
  );

  if (!isValid) {
    alert("Please follow all password rules.");
    return false;
  }

  if (password !== confirmPassword) {
    alert("Passwords do not match.");
    return false;
  }

  return true;
}
