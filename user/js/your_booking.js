
function displayBookings() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('bookingList');
    const rows = table.getElementsByTagName('tr');
    let visibleCount = 0;

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let rowText = '';

        for (let j = 0; j < cells.length; j++) {
            rowText += cells[j].textContent.toLowerCase() + ' ';
        }
        if (rowText.indexOf(input) > -1) {
            rows[i].style.display = '';  
            visibleCount++;
        } else {
            rows[i].style.display = 'none'; 
        }
    }

    document.getElementById('noBookingsMessage').style.display = visibleCount === 0 ? 'block' : 'none';
}

fetch('php/cancel_unpaid_appointments.php')
  .then(res => res.json())
  .then(data => console.log(data.message))
  .catch(err => console.error('Auto cancel failed:', err));

  function toggleDropdown(button) {
        const menu = button.nextElementSibling;
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.text-left')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        }
    });
    let selectedBookingId = null;

    function openPaymentMethodModal(appointmentId, appointmentFee) {
      window.selectedAppointmentId = appointmentId;
      window.selectedAppointmentFee = appointmentFee;
      document.getElementById('paymentMethodModal').classList.remove('hidden');
    }
    

function closePaymentModal() {
  document.getElementById('paymentMethodModal').classList.add('hidden');
}

function selectPaymentMethod(method) {
  document.getElementById('methodName').innerText = method.method_name;
  document.getElementById('methodDetails').innerText = method.details;
  document.getElementById('qrCodeImg').src = '/salon management/admin/uploads/' + method.qr_code;
  document.getElementById('methodAppointmentFee').innerText = window.selectedAppointmentFee;
  document.getElementById('methodContactNumber').innerText = method.contact_number; 

  document.getElementById('paymentMethodModal').classList.add('hidden');
  document.getElementById('paymentInfoModal').classList.remove('hidden');
}

function closePaymentInfoModal() {
  document.getElementById('paymentInfoModal').classList.add('hidden');
}
let currentAppointmentId = null;

function archiveBooking(appointmentId) {
  currentAppointmentId = appointmentId;
  document.getElementById('archiveModal').classList.remove('hidden');
}

document.getElementById('cancelArchiveBtn').addEventListener('click', () => {
  currentAppointmentId = null;
  document.getElementById('archiveModal').classList.add('hidden');
});

function showSuccessToast(message) {
  const toast = document.getElementById('successToast');
  toast.textContent = message;
  toast.classList.remove('opacity-0', 'pointer-events-none');

  setTimeout(() => {
    toast.classList.add('opacity-0', 'pointer-events-none');
  }, 3000); 
}

document.getElementById('confirmArchiveBtn').addEventListener('click', () => {
  if (!currentAppointmentId) return;

  fetch('php/delete_booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ appointment_id: currentAppointmentId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showSuccessToast('Booking archived successfully.');
      document.getElementById('archiveModal').classList.add('hidden');
      currentAppointmentId = null;
      setTimeout(() => {
        location.reload();
      }, 1500);
    } else {
      alert('Failed to archive booking: ' + data.message);
    }
  })
  .catch(err => alert('An error occurred: ' + err.message));
});


const paymentInput = document.querySelector('input[name="payment_screenshot"]');
  const submitBtn = document.getElementById('submitPaymentBtn');

  function closeInvalidFileModal() {
    document.getElementById('invalidFileModal').classList.add('hidden');
  }

  function closePaymentInfoModal() {
    document.getElementById('paymentInfoModal').classList.add('hidden');
    paymentInput.value = '';
    submitBtn.disabled = true;
  }

  paymentInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) {
      submitBtn.disabled = true;
      return;
    }

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    if (!allowedTypes.includes(file.type)) {
      this.value = '';
      submitBtn.disabled = true;
      document.getElementById('invalidFileModal').classList.remove('hidden');
    } else {
      submitBtn.disabled = false;
    }
  });

  
  document.getElementById('invalidFileModal').addEventListener('click', function(e) {
    if (e.target === this) closeInvalidFileModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
      if (!document.getElementById('invalidFileModal').classList.contains('hidden')) {
        closeInvalidFileModal();
      }
      if (!document.getElementById('paymentInfoModal').classList.contains('hidden')) {
        closePaymentInfoModal();
      }
    }
  });
  submitBtn.addEventListener('click', function (e) {
    const file = paymentInput.files[0];
    const bookingId = window.selectedAppointmentId;
  
    if (!file) {
      alert('Please upload a valid image file before submitting.');
      return;
    }
  
    if (!bookingId) {
      alert("Missing booking information.");
      return;
    }
  
    const methodName = document.getElementById('methodName').innerText;
  
    const formData = new FormData();
    formData.append('appointment_id', bookingId);
    formData.append('payment_method_name', methodName);
    formData.append('payment_screenshot', file);
  
    fetch('php/upload_payment_proof.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closePaymentInfoModal(); 
        showSuccessModal(data.message); 
      } else {
        alert(data.message); 
      }
    })
    .catch(err => {
      console.error('Upload failed:', err);
      alert('An error occurred while uploading your proof of payment.');
    });
    
  });
  

  