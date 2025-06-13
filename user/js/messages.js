
let currentEmployeeId = null;
function openModal(employeeId) {
    currentEmployeeId = employeeId;
    loadMessages(employeeId);
    document.getElementById('messageModal').classList.remove('hidden');
    document.getElementById('messageModal').classList.add('flex');
}
function closeModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageModal').classList.remove('flex');
    currentEmployeeId = null;
}
function loadMessages(employeeId) {
    fetch(`get_messages.php?employee_id=${employeeId}`)
        .then(response => response.json())
        .then(data => {
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '';
            if (data.length === 0) {
                modalContent.innerHTML = '<p class="text-gray-600">No messages found.</p>';
            } else {
                data.forEach(msg => {
                    const isEmployee = msg.sender_role === 'employee';
                    const alignment = isEmployee ? 'items-start justify-start' : 'items-end justify-end';
                    const bubbleColor = isEmployee ? 'bg-gray-200 text-black' : 'bg-blue-500 text-white';
                    const msgElement = `
                        <div class="flex ${alignment}">
                            <div class="max-w-[75%] px-4 py-2 mb-2 rounded-lg ${bubbleColor}">
                                <p class="text-sm">${msg.message}</p>
                                <p class="text-xs text-right opacity-80">${msg.sent_at}</p>
                            </div>
                        </div>`;
                    modalContent.innerHTML += msgElement;
                });
                modalContent.scrollTop = modalContent.scrollHeight;
            }
        });
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message || !currentEmployeeId) return;
    fetch('send_message_to_employee.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ employee_id: currentEmployeeId, message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentEmployeeId);
        } else {
            alert(data.error || 'Failed to send message.');
        }
    });
}