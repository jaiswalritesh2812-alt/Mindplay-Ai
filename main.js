// MindPlay JavaScript Functions

// Show notification
function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `notification ${type === "success" ? "bg-green-500" : "bg-red-500"} text-white`;
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Confirm quiz submission
function confirmQuizSubmit(event) {
  if (
    !confirm(
      "Are you sure you want to submit your quiz? You cannot change your answers after submission.",
    )
  ) {
    event.preventDefault();
    return false;
  }
  return true;
}

// Auto-save form data to localStorage
function autoSaveForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return;

  const formData = new FormData(form);
  const data = {};

  formData.forEach((value, key) => {
    data[key] = value;
  });

  localStorage.setItem(formId + "_autosave", JSON.stringify(data));
}

// Restore form data from localStorage
function restoreForm(formId) {
  const savedData = localStorage.getItem(formId + "_autosave");
  if (!savedData) return;

  const form = document.getElementById(formId);
  if (!form) return;

  const data = JSON.parse(savedData);

  Object.keys(data).forEach((key) => {
    const input = form.querySelector(`[name="${key}"]`);
    if (input) {
      input.value = data[key];
    }
  });
}

// Clear form autosave
function clearFormAutosave(formId) {
  localStorage.removeItem(formId + "_autosave");
}

// Quiz timer
class QuizTimer {
  constructor(duration, displayElement) {
    this.duration = duration; // in seconds
    this.displayElement = displayElement;
    this.timeLeft = duration;
    this.timer = null;
  }

  start() {
    this.timer = setInterval(() => {
      this.timeLeft--;
      this.updateDisplay();

      if (this.timeLeft <= 0) {
        this.stop();
        this.onTimeUp();
      }
    }, 1000);
  }

  stop() {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
    }
  }

  updateDisplay() {
    const minutes = Math.floor(this.timeLeft / 60);
    const seconds = this.timeLeft % 60;
    this.displayElement.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;
  }

  onTimeUp() {
    alert("Time is up! Your quiz will be submitted automatically.");
    document.getElementById("quizForm")?.submit();
  }
}

// Loading spinner
function showLoading() {
  const spinner = document.createElement("div");
  spinner.id = "loading-spinner";
  spinner.className =
    "fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-50 z-50";
  spinner.innerHTML = '<div class="spinner"></div>';
  document.body.appendChild(spinner);
}

function hideLoading() {
  const spinner = document.getElementById("loading-spinner");
  if (spinner) {
    spinner.remove();
  }
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  const requiredFields = form.querySelectorAll("[required]");
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("border-red-500");
      isValid = false;
    } else {
      field.classList.remove("border-red-500");
    }
  });

  return isValid;
}

// Copy to clipboard
function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showNotification("Copied to clipboard!", "success");
    })
    .catch(() => {
      showNotification("Failed to copy", "error");
    });
}

// Initialize tooltips
function initTooltips() {
  const tooltips = document.querySelectorAll("[data-tooltip]");
  tooltips.forEach((element) => {
    element.addEventListener("mouseenter", function () {
      const tooltip = document.createElement("div");
      tooltip.className =
        "absolute bg-gray-800 text-white px-2 py-1 rounded text-sm";
      tooltip.textContent = this.getAttribute("data-tooltip");
      tooltip.style.top = this.offsetTop - 30 + "px";
      tooltip.style.left = this.offsetLeft + "px";
      document.body.appendChild(tooltip);

      this.addEventListener("mouseleave", function () {
        tooltip.remove();
      });
    });
  });
}

// Search functionality
function searchTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);

  if (!input || !table) return;

  input.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName("td");
      let found = false;

      for (let j = 0; j < cells.length; j++) {
        if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
          found = true;
          break;
        }
      }

      rows[i].style.display = found ? "" : "none";
    }
  });
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  // Add smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth" });
      }
    });
  });

  // Initialize tooltips
  initTooltips();

  // Add animation class to cards
  document.querySelectorAll(".card").forEach((card, index) => {
    setTimeout(() => {
      card.classList.add("quiz-card");
    }, index * 100);
  });
});
