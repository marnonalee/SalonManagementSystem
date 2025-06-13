
let selectedAgent = ""; 
const cards = document.querySelectorAll('.service-card');
const servicesContainer = document.getElementById('servicesContainer');
const modal = document.getElementById('bookingModal');
const closeModal = document.getElementById('closeModal');
const bookNowBtns = document.querySelectorAll('.bookNowBtn');
const datePicker = document.getElementById('datePicker');
const today = new Date();

bookNowBtns.forEach(button => {
    button.addEventListener('click', function() {
        modal.classList.remove('hidden');
        document.getElementById('serviceOption').value = this.dataset.service;
        fetchEmployeesByService(this.dataset.service);
    });
});

closeModal.addEventListener('click', function() {
    modal.classList.add('hidden');
    resetBookingForm();
});

document.addEventListener("DOMContentLoaded", function () {
    const dayMap = {
    "Sunday": 0,
    "Monday": 1,
    "Tuesday": 2,
    "Wednesday": 3,
    "Thursday": 4,
    "Friday": 5,
    "Saturday": 6
    };

    fetch("php/get_open_days.php")
    .then(response => response.json())
    .then(openDays => {
        const allowedDays = openDays.map(day => dayMap[day]);

        flatpickr("#datePicker", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(2),
        disable: [
            function (date) {
            return !allowedDays.includes(date.getDay());
            }
        ]
        });
    })
    .catch(error => {
        console.error("Error loading open days:", error);
    });
});
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault();
});

function validateForm() {
    const serviceInput = document.getElementById("serviceOption");
    const service = serviceInput.value;
    const date = document.getElementById('datePicker').value;
    const time = document.getElementById('timePicker').value;

    if (!service || !date || !time || !selectedAgent) {
        alert('Please fill out all fields including selecting an agent!');
        return;
    }

    const selectedServiceData = serviceData[service];
    if (!selectedServiceData) {
        alert('Invalid service selected.');
        return;
    }

    document.getElementById('summaryService').textContent = service;
    document.getElementById('summaryDate').textContent = date;
    document.getElementById('summaryTime').textContent = time;
    document.getElementById('summaryAgent').textContent = selectedAgent;
    document.getElementById('summaryPrice').textContent = parseFloat(selectedServiceData.price).toFixed(2);
    document.getElementById('summaryFee').textContent = parseFloat(selectedServiceData.fee).toFixed(2);
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('summaryModal').classList.remove('hidden');
}

function showAgents() {
    document.getElementById('agentSelection').classList.remove('hidden');
    document.getElementById('continueButton').classList.add('hidden');
}
function selectAgent(element) {
    const selectedName = element.innerText.trim();

    document.querySelectorAll('.agent-card').forEach(card => {
        card.classList.remove('border-slate-500', 'ring-2', 'ring-slate-500');
    });
    element.classList.add('border-slate-500', 'ring-2', 'ring-slate-500');

    selectedAgent = selectedName;

    const selectedDate = document.getElementById("datePicker").value;
    if (!selectedDate) {
        alert("Please select a date first!");
        return;
    }

    const serviceInput = document.getElementById("serviceOption");
const selectedServiceName = serviceInput.value;
const selectedServiceData = serviceData[selectedServiceName];
const serviceDuration = selectedServiceData ? selectedServiceData.duration : 0;

if (serviceDuration === 0) {
    alert("Please select a valid service!");
    return;
}


    document.getElementById('timePickerWrapper').classList.remove('hidden');

    fetch(`php/get_employee_time.php?employee=${encodeURIComponent(selectedName)}&date=${selectedDate}&duration=${serviceDuration}`)
        .then(response => response.json())
        .then(times => {
            const timePicker = document.getElementById('timePicker');
            timePicker.innerHTML = '<option value="">Select Time</option>';

            if (times.length === 0) {
                timePicker.innerHTML += '<option value="">No available time slots</option>';
            } else {
                times.forEach(time => {
                    timePicker.innerHTML += `<option value="${time}">${time}</option>`;
                });
            }
        })
        .catch(error => {
            console.error("Error fetching times:", error);
            const timePicker = document.getElementById('timePicker');
            timePicker.innerHTML = '<option value="">Error loading times</option>';
        });
}
function populateTimes(times) {
    const timePicker = document.getElementById('timePicker');
    timePicker.innerHTML = '<option value="">Select Time</option>';
    if (times.length === 0) {
        timePicker.innerHTML += '<option value="">No available time</option>';
    } else {
        times.forEach(time => {
            timePicker.innerHTML += `<option value="${time}">${time}</option>`;
        });
    }
}


function proceedBooking() {
    const serviceSelect = document.getElementById('serviceOption');
    const selectedOption = serviceSelect.value;
    const selectedDate = document.getElementById('datePicker').value;
    const selectedTime = document.getElementById('timePicker').value;
    const selectedAgent = document.querySelector('.agent-card.border-slate-500 p').textContent;

    const selectedOptionElement = serviceSelect.options[serviceSelect.selectedIndex];
    const price = selectedOptionElement.getAttribute('data-price');

    document.getElementById('summaryService').textContent = selectedOption;
    document.getElementById('summaryDate').textContent = selectedDate;
    document.getElementById('summaryTime').textContent = selectedTime;
    document.getElementById('summaryAgent').textContent = selectedAgent;
    document.getElementById('summaryPrice').textContent = parseFloat(price).toFixed(2);

    document.getElementById("summaryModal").classList.remove("hidden");
    document.getElementById("bookingModal").classList.add("hidden");
}

function changeBookingOption() {
    document.getElementById("summaryModal").classList.add("hidden");
    modal.classList.remove("hidden");
}
function resetBookingForm() {
    document.getElementById('serviceOption').value = '';
    document.getElementById('datePicker').value = '';
    document.getElementById('timePicker').value = '';
    
    document.querySelectorAll('.agent-card').forEach(agent => agent.classList.remove('border-4', 'border-slate-600'));
    document.getElementById('agentSelection').classList.add('hidden');
    document.getElementById('proceedButton').classList.add('hidden');
    document.getElementById('continueButton').classList.remove('hidden');

    document.getElementById('serviceError').classList.add('hidden');
    document.getElementById('dateError').classList.add('hidden');
    document.getElementById('timeError').classList.add('hidden');
}

const closeSummaryModal = document.getElementById('closeSummaryModal');
const confirmCancelModal = document.getElementById('confirmCancelModal');
const confirmYes = document.getElementById('confirmYes');
const confirmNo = document.getElementById('confirmNo');

closeSummaryModal.addEventListener('click', function () {
    confirmCancelModal.classList.remove('hidden');
});

confirmYes.addEventListener('click', function () {
    confirmCancelModal.classList.add('hidden');
    document.getElementById("summaryModal").classList.add("hidden");
    resetBookingForm();
});

confirmNo.addEventListener('click', function () {
    confirmCancelModal.classList.add('hidden');
});

document.getElementById('serviceOption').addEventListener('change', function() {
    const service = this.value;
    if (service) {
        fetchEmployeesByService(service);
    }
});

function fetchEmployeesByServiceAndDate(service, date) {
    if (!service || !date) {
        document.getElementById('agentSelection').innerHTML = '<p>Please select both service and date.</p>';
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `php/fetch_employees.php?service=${encodeURIComponent(service)}&date=${encodeURIComponent(date)}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('agentSelection').innerHTML = xhr.responseText;
            document.getElementById('agentSelection').classList.remove('hidden');
        }
    };
    xhr.send();
}

document.getElementById('serviceOption').addEventListener('change', () => {
    const service = document.getElementById('serviceOption').value;
    const date = document.getElementById('datePicker').value;
    fetchEmployeesByServiceAndDate(service, date);
});

document.getElementById('datePicker').addEventListener('change', () => {
    const service = document.getElementById('serviceOption').value;
    const date = document.getElementById('datePicker').value;
    fetchEmployeesByServiceAndDate(service, date);
});


function payLater() {
    const service = document.getElementById('summaryService').textContent;
    const date = document.getElementById('summaryDate').textContent;
    const fullTime = document.getElementById('summaryTime').textContent;
    const start_time = fullTime.split(' - ')[0]; 
    const agent = document.getElementById('summaryAgent').textContent;
    const price = document.getElementById('summaryPrice').textContent;
    const fee = document.getElementById('summaryFee').textContent;

    const data = {
        service,
        date,
        time: start_time, 
        agent,
        price,
        fee,
        pay_later: true  
    };

    fetch('php/save_appointment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data),
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(response => {
        if(response.success) {
            document.getElementById('summaryModal').classList.add('hidden');
            document.getElementById('appointmentSuccessModal').classList.remove('hidden');
        } else {
            alert('Failed to book appointment: ' + response.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred while booking your appointment.');
    });
}

function closeAppointmentSuccessModal() {
    document.getElementById('appointmentSuccessModal').classList.add('hidden');
}

function closeAppointmentSuccessModal() {
    document.getElementById('appointmentSuccessModal').classList.add('hidden');

    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) bookingForm.reset();

    document.getElementById('bookingModal').classList.add('hidden');

    document.getElementById('agentSelection')?.classList.add('hidden');
    document.getElementById('timePickerWrapper')?.classList.add('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');

    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) bookingForm.reset();

    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('agentSelection')?.classList.add('hidden');
    document.getElementById('timePickerWrapper')?.classList.add('hidden');
}

const summaryModal = document.getElementById('summaryModal');
const paymentMethodsModal = document.getElementById('paymentMethodsModal');
const payNowButton = document.getElementById('payNowButton');
const closePaymentMethodsModalBtn = document.getElementById('closePaymentMethodsModal');
const paymentMethodsList = document.getElementById('paymentMethodsList');
const confirmPaymentMethodBtn = document.getElementById('confirmPaymentMethodBtn');

const paymentDetailsModal = document.getElementById('paymentDetailsModal');
const paymentMethodName = document.getElementById('paymentMethodName');
const paymentMethodQR = document.getElementById('paymentMethodQR');
const paymentMethodDetails = document.getElementById('paymentMethodDetails');
const paymentMethodContact_Number = document.getElementById('paymentMethodContact_Number');
const closePaymentDetailsModal = document.getElementById('closePaymentDetailsModal');

payNowButton.addEventListener('click', () => {
payNow();
summaryModal.classList.add('hidden');
});

closePaymentMethodsModalBtn.addEventListener('click', () => {
paymentMethodsModal.classList.add('hidden');
summaryModal.classList.remove('hidden');
});

let loadingPaymentMethods = false;

function loadPaymentMethods() {
  if (loadingPaymentMethods) return; 
  loadingPaymentMethods = true;

  paymentMethodsList.innerHTML = '';
  confirmPaymentMethodBtn.disabled = true;

  fetch('php/get_payment_methods.php')
    .then(response => response.json())
    .then(data => {
      if (!data || data.length === 0) {
        paymentMethodsList.innerHTML = '<p class="text-center text-gray-500">No payment methods available.</p>';
        return;
      }
      data.forEach(method => {
        const label = document.createElement('label');
        label.classList.add('flex', 'items-center', 'space-x-3', 'cursor-pointer');
        label.innerHTML = `
          <input type="radio" name="paymentMethod" value="${method.payment_method_id}" />
          <span>${method.method_name}</span>
        `;

        paymentMethodsList.appendChild(label);
      });

      paymentMethodsList.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', () => {
          confirmPaymentMethodBtn.disabled = false;
        });
      });

      paymentMethodsModal.classList.remove('hidden');
    })
    .catch(err => {
      console.error(err);
      paymentMethodsList.innerHTML = '<p class="text-red-500 text-center">Failed to load payment methods.</p>';
      paymentMethodsModal.classList.remove('hidden');
    })
    .finally(() => {
      loadingPaymentMethods = false; 
    });
}


confirmPaymentMethodBtn.addEventListener('click', () => {
    
    const selectedMethod = paymentMethodsList.querySelector('input[name="paymentMethod"]:checked');
    if (!selectedMethod) return;
  
    const paymentMethodId = selectedMethod.value;
    console.log('Selected paymentMethodId:', paymentMethodId); 
  
    fetch(`php/get_payment_method_details.php?id=${paymentMethodId}`)
      .then(response => response.json())
      .then(method => {
        console.log('Payment method details received:', method);
        if (method.error) {
          alert(method.error);
          return;
        }
      
        paymentMethodName.textContent = method.method_name || 'N/A';
        paymentMethodDetails.textContent = method.details || 'No instructions provided.';
        paymentMethodQR.src = method.qr_code ? `/salon management/admin/uploads/${method.qr_code}?t=${Date.now()}` : '';
      
        paymentMethodContact_Number.textContent = method.contact_number
          ? `Contact Number: ${method.contact_number}`
          : 'No contact number provided.';
      
        paymentDetailsModal.classList.remove('hidden');
        paymentMethodsModal.classList.add('hidden');
      })
      
      .catch(err => {
        alert('Failed to load payment details.');
        console.error(err);
        
      });
  });
  
closePaymentDetailsModal.addEventListener('click', () => {
paymentDetailsModal.classList.add('hidden');
paymentMethodsModal.classList.remove('hidden');
});


function payNow() {
const button = document.getElementById('payNowButton');
button.disabled = true; 

const service = document.getElementById('summaryService').textContent;
const date = document.getElementById('summaryDate').textContent;
const fullTime = document.getElementById('summaryTime').textContent;
const start_time = fullTime.split(' - ')[0];
const agent = document.getElementById('summaryAgent').textContent;
const price = document.getElementById('summaryPrice').textContent;
const fee = document.getElementById('summaryFee').textContent;

const data = {
    service,
    date,
    time: start_time,
    agent,
    price,
    fee,
    pay_now: true
};

fetch('php/save_appointment.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data),
    credentials: 'same-origin'
})
.then(res => res.json())
.then(response => {
    if (response.success) {
        window.savedAppointmentId = response.appointment_id;
        document.getElementById('summaryModal').classList.add('hidden');
        loadPaymentMethods();
    } else {
        alert('Failed to save appointment: ' + response.message);
    }
})
.catch(err => {
    console.error('Error:', err);
    alert('Error saving appointment.');
})
.finally(() => {
    button.disabled = false; 
});
}
function proceedAfterViewing() {
const fileInput = document.getElementById('paymentProof');
const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked');

if (!fileInput.files.length || !selectedMethod || !window.savedAppointmentId) {
    alert("Missing payment proof or payment method.");
    return;
}

const formData = new FormData();
formData.append('appointment_id', window.savedAppointmentId);
formData.append('payment_method_id', selectedMethod.value);
formData.append('payment_proof', fileInput.files[0]);
formData.append('payment_type', 'Pay Now');

fetch('php/save_payment_proof.php', {
    method: 'POST',
    body: formData
})
.then(async res => {
    const text = await res.text();
    try {
        const json = JSON.parse(text);
        if (json.success) {
            document.getElementById('successModal').classList.remove('hidden');
            document.getElementById('paymentDetailsModal').classList.add('hidden');
        } else {
            alert('Failed to upload proof: ' + json.message);
        }
    } catch (e) {
        console.error('Raw response:', text);
        alert('An error occurred while uploading proof (not JSON): ' + e.message);
    }
})
.catch(err => {
    console.error('Upload error:', err);
    alert('An error occurred while uploading proof: ' + err.message);
});
}


