let timeout;

function retroceder10(event) {
  event.stopPropagation();
  const pos = player.getPosition();
  player.seek(Math.max(pos + 10, 0));
}

function adelantar10(event) {
  event.stopPropagation();
  const pos = player.getPosition();
  const duracion = player.getDuration();
  player.seek(Math.min(pos - 10, duracion - 1));
}

function togglePlayPause(event) {
  event.stopPropagation();
  const icon = document.getElementById("playpause-icon");
  const state = player.getState();
  if (state === "playing") {
    player.pause();
    icon.src = "img/play_button.png";
  } else {
    player.play();
    icon.src = "img/paused_button.png";
  }
}

function showButtons() {
  document.querySelectorAll('.control-btn').forEach(btn => btn.style.opacity = 1);
  if (player.getState() === "playing") {
    clearTimeout(timeout);
    timeout = setTimeout(hideButtons, 3000);
  }
}

function hideButtons() {
  if (player.getState() !== "paused") {
    document.querySelectorAll('.control-btn').forEach(btn => btn.style.opacity = 0);
  }
}
