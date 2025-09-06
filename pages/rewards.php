 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scratch & Save</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #ffffff;
      font-family: 'Segoe UI', sans-serif;
    }

    .container {
      max-width: 400px;
      margin-top: 60px;
    }

    .scratch-box {
      position: relative;
      width: 100%;
      height: 250px;
      background-color: #008080;
      border-radius: 20px;
      overflow: hidden;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      user-select: none;
    }

    canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 5;
      border-radius: 20px;
    }

    .reward-text {
      z-index: 1;
    }

    .reward-msg {
      display: none;
      font-size: 20px;
      color: #008080;
      font-weight: bold;
      margin-top: 20px;
    }

    .reward-msg.show {
      display: block;
    }

    .tap-text {
      position: absolute;
      z-index: 6;
      color: #000;
      background: rgba(255, 255, 255, 0.8);
      font-size: 18px;
      padding: 6px 12px;
      border-radius: 12px;
      top: 10px;
    }

    .cooldown-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: white;
      border-radius: 20px;
    }

    .cooldown-timer {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .pls-wait { font-size: 13px; }
  </style>
</head>
<body>

<div class="container text-center">
  <h4 class="mb-3"> 🎁 Scratch & Save Your Bill </h4>
  <p class="text-muted"> Minimum bill ₹500 required to use this discount card </p>

  <div class="scratch-box mx-auto" id="scratchBox">
    <div class="reward-text" id="rewardText">₹<span id="rewardAmount">0</span> Discount!</div>
    <canvas id="scratchCanvas" width="400" height="250"></canvas>
    <div class="tap-text" id="tapText">👆 Tap & Scratch</div>
    <div class="cooldown-overlay" id="cooldownOverlay" style="display: none;">
      <div class="cooldown-timer" id="cooldownTimer">05:00</div>
      <div class="pls-wait">Please wait before scratching again</div>
    </div>
  </div>

  <div class="reward-msg" id="rewardMsg">
    🎉 You got ₹<span id="finalReward"></span> off!<br>
    👉 Show this screen to your service provider.
  </div>

  <div class="text-center mt-3">
      <button class="btn btn-warning rounded-pill px-4">Tell your friends</button>
 </div>

        <ul class="mt-4 small text-muted list-unstyled">
            <li>• Scratch to see your winning amount</li>
            <li>• Use this amount before 24 Jan 2021</li>
            <li>• This amount is only applicable to selected categories</li>
        </ul>

</div>

<!-- Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
  const canvas = document.getElementById("scratchCanvas");
  const ctx = canvas.getContext("2d");
  const rewardAmount = document.getElementById("rewardAmount");
  const finalReward = document.getElementById("finalReward");
  const rewardMsg = document.getElementById("rewardMsg");
  const tapText = document.getElementById("tapText");
  const cooldownOverlay = document.getElementById("cooldownOverlay");
  const cooldownTimer = document.getElementById("cooldownTimer");

  let isDrawing = false;
  let scratchedPercent = 0;
  let hasScratched = false;
  let cooldownInterval;
  const COOLDOWN_TIME = 5 * 60 * 1000; // 5 minutes in milliseconds

  // Check if cooldown is active from localStorage
  checkCooldown();

  function checkCooldown() {
    const lastScratchTime = localStorage.getItem('lastScratchTime');
    if (lastScratchTime) {
      const timePassed = Date.now() - parseInt(lastScratchTime);
      if (timePassed < COOLDOWN_TIME) {
        // Cooldown is active
        startCooldown(COOLDOWN_TIME - timePassed);
        return true;
      } else {
        // Cooldown finished
        localStorage.removeItem('lastScratchTime');
        return false;
      }
    }
    return false;
  }

  function startCooldown(remainingTime) {
    // Disable scratching
    canvas.style.pointerEvents = 'none';
    cooldownOverlay.style.display = 'flex';
    
    let timeLeft = remainingTime || COOLDOWN_TIME;
    
    // Update timer immediately
    updateTimerDisplay(timeLeft);
    
    cooldownInterval = setInterval(() => {
      timeLeft -= 1000;
      updateTimerDisplay(timeLeft);
      
      if (timeLeft <= 0) {
        clearInterval(cooldownInterval);
        cooldownOverlay.style.display = 'none';
        canvas.style.pointerEvents = 'auto';
        localStorage.removeItem('lastScratchTime');
        
        // Reset scratch card
        resetScratchCard();
      }
    }, 1000);
  }

  function updateTimerDisplay(time) {
    const minutes = Math.floor(time / 60000);
    const seconds = Math.floor((time % 60000) / 1000);
    cooldownTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  }

  function resetScratchCard() {
    // Reset canvas
    ctx.globalCompositeOperation = "source-over";
    ctx.fillStyle = "#008091";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.globalCompositeOperation = "destination-out";
    
    // Reset variables
    hasScratched = false;
    scratchedPercent = 0;
    
    // Hide reward message
    rewardMsg.classList.remove("show");
    
    // Show tap text
    tapText.style.display = "block";
    canvas.style.display = "block";
    
    // Generate new reward
    const min = 20, max = 30;
    const reward = Math.floor(Math.random() * (max - min + 1)) + min;
    rewardAmount.innerText = reward;
    finalReward.innerText = reward;
  }

  // Random reward between 20 and 30
  const min = 20, max = 30;
  const reward = Math.floor(Math.random() * (max - min + 1)) + min;
  rewardAmount.innerText = reward;
  finalReward.innerText = reward;

  // Fill canvas with gray "scratch" layer
  ctx.fillStyle = "#008091";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.globalCompositeOperation = "destination-out";

  function getFilledPercentage() {
    const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
    let transparentPixels = 0;
    for (let i = 0; i < pixels.data.length; i += 4) {
      if (pixels.data[i + 3] === 0) transparentPixels++;
    }
    return transparentPixels / (canvas.width * canvas.height) * 100;
  }

  function scratch(e) {
    if (!isDrawing || hasScratched) return;
    const rect = canvas.getBoundingClientRect();
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
    ctx.beginPath();
    ctx.arc(x, y, 20, 0, Math.PI * 2);
    ctx.fill();

    scratchedPercent = getFilledPercentage();
    if (scratchedPercent > 50 && !hasScratched) {
      hasScratched = true;
      canvas.style.display = "none";
      tapText.style.display = "none";
      rewardMsg.classList.add("show");

      // Store scratch time in localStorage
      localStorage.setItem('lastScratchTime', Date.now().toString());
      
      // Start cooldown
      startCooldown();

      // Fire confetti
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#008080', '#00bfa6', '#ffcc00']
      });
    }
  }

  canvas.addEventListener("mousedown", () => isDrawing = true);
  canvas.addEventListener("mouseup", () => isDrawing = false);
  canvas.addEventListener("mousemove", scratch);

  canvas.addEventListener("touchstart", () => isDrawing = true);
  canvas.addEventListener("touchend", () => isDrawing = false);
  canvas.addEventListener("touchmove", scratch);
</script>

</body>
</html