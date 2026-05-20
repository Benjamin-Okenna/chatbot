// register-biometrics.js

const video = document.getElementById('webcam');
const canvas = document.getElementById('canvas');
const captureBtn = document.getElementById('capture-btn');
const retakeBtn = document.getElementById('retake-btn');
const previewImg = document.getElementById('captured-preview');
const bioInput = document.getElementById('biometric_image');
const successStatus = document.getElementById('success-status');
const regForm = document.getElementById('regForm');

// 1. Fire up the student's system webcam stream
navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 }, audio: false })
    .then(stream => {
        video.srcObject = stream;
    })
    .catch(err => {
        alert("Camera Access Error: This portal requires webcam access to register your biometric identity profile.");
        console.error(err);
    });

// 2. Take a snapshot of the live camera feed
captureBtn.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    
    // Draw the current video frame onto our hidden memory canvas
    context.drawImage(video, 0, 0, 320, 240);
    
    // Convert the canvas image into a base64 encoded DataURL text string
    const dataUrl = canvas.toDataURL('image/jpeg');
    
    // Shove the string into our hidden form element input row
    bioInput.value = dataUrl;
    
    // Swap interface visibilities to freeze preview on screen
    previewImg.src = dataUrl;
    video.style.display = 'none';
    previewImg.style.display = 'block';
    captureBtn.style.display = 'none';
    retakeBtn.style.display = 'inline-block';
    successStatus.style.display = 'block';
});

// 3. Clear snapshot to re-try capture pipeline
retakeBtn.addEventListener('click', () => {
    bioInput.value = '';
    previewImg.src = '';
    previewImg.style.display = 'none';
    video.style.display = 'block';
    retakeBtn.style.display = 'none';
    captureBtn.style.display = 'inline-block';
    successStatus.style.display = 'none';
});

// 4. Client-side Form Validation Guard
regForm.addEventListener('submit', (e) => {
    if (!bioInput.value) {
        e.preventDefault();
        alert("Biometric Validation Failed: You must snap your profile picture before completing account enrollment.");
    }
});