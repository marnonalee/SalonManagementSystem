
      document.getElementById('searchInput').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        let anyVisible = false;

        rows.forEach(row => {
          const rowText = row.innerText.toLowerCase();
          const match = rowText.includes(filter);
          row.style.display = match ? '' : 'none';
          if (match) anyVisible = true;
        });

        document.getElementById('noResultsMessage').classList.toggle('hidden', anyVisible);
      });

      const addEmployeeBtn = document.getElementById('addEmployeeBtn');
      const addEmployeeModal = document.getElementById('addEmployeeModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const passwordInput = document.getElementById('password');

      function generateStructuredPassword() {
        const letters = "abcdefghijklmnopqrstuvwxyz";
        const randomLetter = () => letters[Math.floor(Math.random() * letters.length)];
        const randomDigit = () => Math.floor(Math.random() * 10);
        return `salon${randomLetter()}${randomDigit()}${randomLetter()}${randomLetter()}`;
      }

      addEmployeeBtn.addEventListener('click', () => {
        const generatedPassword = generateStructuredPassword();
        passwordInput.value = generatedPassword;
        addEmployeeModal.classList.remove('hidden');
      });

      closeModalBtn.addEventListener('click', () => {
        addEmployeeModal.classList.add('hidden');
      });

      window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
          addEmployeeModal.classList.add('hidden');
        }
      });

      function archiveEmployee(employeeId, row) {
        fetch('php/archive_employee_handler.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'employee_id=' + employeeId
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            showSuccessModal();
            row.remove();
          } else {
            alert("Failed to archive employee.");
          }
        })
        .catch(() => alert("Network error."));
      }

      function showSuccessModal() {
        const modal = document.getElementById('employeeArchiveModal');
        modal.style.display = 'flex';

        document.getElementById('closeArchiveModalBtn').onclick = () => {
          modal.style.display = 'none';
          location.reload();
        };
      }

      function restoreEmployee(employeeId, row) { 
        fetch('php/restore_employee.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'employee_id=' + employeeId
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === 'success') {
            showRestoreModal();
            row.remove();  
          } else {
            alert("Failed to restore employee.");
          }
        })
        .catch(() => alert("Network error."));
      }

      function showRestoreModal() {
        const modal = document.getElementById('employeeRestoreModal');
        modal.style.display = 'flex';

        document.getElementById('closeRestoreModalBtn').onclick = () => {
          modal.style.display = 'none';
          location.reload();
        };
      }

      function editEmployee(id) {
      const row = document.querySelector(`button[onclick="editEmployee(${id})"]`).closest('tr');

      const name = row.children[1].textContent.trim();
      const specialization = row.children[2].textContent.trim();
      const email = row.children[3].textContent.trim();
      const startTime = convertTo24Hour(row.children[5].textContent.trim());
      const endTime = convertTo24Hour(row.children[6].textContent.trim());

      document.getElementById('edit_employee_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_specialization').value = specialization;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_start_time').value = startTime;
      document.getElementById('edit_end_time').value = endTime;

      document.getElementById('editEmployeeModal').classList.remove('hidden');
    }

    function convertTo24Hour(timeStr) {
      const [time, modifier] = timeStr.split(' ');
      let [hours, minutes] = time.split(':');
      if (hours === '12') hours = '00';
      if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
      return `${hours.toString().padStart(2,'0')}:${minutes}`;
    }

    function closeEditModal() {
      document.getElementById('editEmployeeModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

      if (status === 'success') {
        const successModal = document.getElementById('successModal');
        successModal.classList.remove('hidden');

        document.getElementById('closeSuccessModalBtn').onclick = () => {
          successModal.classList.add('hidden');
          const cleanUrl = window.location.origin + window.location.pathname;
          window.history.replaceState({}, document.title, cleanUrl);
        };
      }
    });
      function showSuccessModal() {
      const modal = document.getElementById('employeeArchiveModal');
      modal.style.display = 'flex';

      document.getElementById('closeArchiveModalBtn').onclick = () => {
        modal.style.display = 'none';
        window.location.href = window.location.pathname;
      };
    }

    if (closeSuccessModalBtn) {
      closeSuccessModalBtn.addEventListener('click', () => {
        document.getElementById('successModal').classList.add('hidden');
      });
    }

    window.addEventListener('click', (e) => {
      if (e.target === document.getElementById('successModal')) {
        document.getElementById('successModal').classList.add('hidden');
      }
    });

    function formatAMPM(timeStr) {
      const [hour, minute] = timeStr.split(':');
      let h = parseInt(hour);
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return `${h}:${minute} ${ampm}`;
    }

    document.getElementById('start_time').addEventListener('input', function() {
      document.getElementById('startTimePreview').textContent = formatAMPM(this.value);
    });

    document.getElementById('end_time').addEventListener('input', function() {
      document.getElementById('endTimePreview').textContent = formatAMPM(this.value);
    });

    function confirmDelete(employeeId) {
    document.getElementById('delete_employee_id').value = employeeId;
    document.getElementById('confirmDeleteModal').classList.remove('hidden');
  }
  function closeDeleteModal() {
    document.getElementById('confirmDeleteModal').classList.add('hidden');
  }
