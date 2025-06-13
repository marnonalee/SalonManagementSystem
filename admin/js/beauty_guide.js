
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'deleted') {
        const successModal = document.getElementById('successModal');
        const successMessage = document.getElementById('successMessage');
        successMessage.textContent = "Beauty guide deleted successfully!";
        successModal.classList.remove('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});


    document.getElementById('searchInput').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const cards = document.querySelectorAll('.guide-card');
        let anyVisible = false;

        cards.forEach(card => {
        const cardText = card.innerText.toLowerCase();
        const match = cardText.includes(filter);
        card.style.display = match ? '' : 'none';
        if (match) anyVisible = true;
        });

        document.getElementById('noResultsMessage').classList.toggle('hidden', anyVisible);

    });

    let currentId = null;
    function openModal(title, content, media, id) {
        currentId = id; 
        document.getElementById('beautyGuideModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = title;
        content = content.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\r\n/g, '\n');
        document.getElementById('modalText').innerHTML = content.replace(/\n/g, '<br>');

        document.getElementById('modalTitle').setAttribute('contenteditable', 'false');
        document.getElementById('modalText').setAttribute('contenteditable', 'false');
        document.getElementById('editBtn').classList.remove('hidden');
        document.getElementById('saveBtn').classList.add('hidden');

        const modalImage = document.getElementById('modalImage');
        const imageInput = document.getElementById('imageInput');
        
        if (media) {
            modalImage.src = media;
            modalImage.style.display = 'block';
        } else {
            modalImage.style.display = 'none';
        }

        imageInput.value = ''; 
        imageInput.classList.add('hidden');
    }

    document.getElementById('editBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').setAttribute('contenteditable', 'true');
        document.getElementById('modalText').setAttribute('contenteditable', 'true');
        document.getElementById('editBtn').classList.add('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        
        document.getElementById('imageInput').classList.remove('hidden');
    });

    document.getElementById('saveBtn').addEventListener('click', () => {
        const updatedTitle = document.getElementById('modalTitle').innerText.trim();
        const updatedContent = document.getElementById('modalText').innerHTML.trim()
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/&nbsp;/g, ' ');

        const updatedImage = document.getElementById('modalImage').src;

        const imageInput = document.getElementById('imageInput');
        let updatedImageFile = updatedImage;

        if (imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const reader = new FileReader();
            reader.onloadend = () => {
                updatedImageFile = reader.result;  
                saveData(updatedTitle, updatedContent, updatedImageFile);
            };
            reader.readAsDataURL(file);
        } else {
            saveData(updatedTitle, updatedContent, updatedImageFile);
        }
    });

    function saveData(updatedTitle, updatedContent, updatedImageFile) {
    fetch('php/update_guide.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
            id: currentId, 
            title: updatedTitle, 
            content: updatedContent, 
            media: updatedImageFile 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById('beautyGuideModal').classList.add('hidden');
            showUpdateSuccessModal();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function showUpdateSuccessModal() {
  const modal = document.getElementById('updateSuccessModal');
  modal.classList.remove('hidden');
}

function closeUpdateSuccessModal() {
  const modal = document.getElementById('updateSuccessModal');
  modal.classList.add('hidden');
}


    document.querySelectorAll('.read-more').forEach(link => {
        link.addEventListener('click', () => {
            const title = link.dataset.title;
            const content = link.dataset.content;
            const media = link.dataset.media || null;
            const id = link.dataset.id;
            openModal(title, content, media, id);
        });
    });

    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('beautyGuideModal').classList.add('hidden');
    });

    document.getElementById('cancelBtn').addEventListener('click', () => {
        document.getElementById('beautyGuideModal').classList.add('hidden');
    });

   

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    function toggleModal(show) {
            const modal = document.getElementById('beautyGuideModal');
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
    }

    document.getElementById('closeModal').addEventListener('click', () => {
        toggleModal(false);
    });

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('beautyGuideModal');
        if (e.target === modal) {
            toggleModal(false);
        }
    });

    function toggleModal(show) {
        const modal = document.getElementById('addGuideModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    function confirmDelete(id) {
    document.getElementById('deleteGuideId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}






