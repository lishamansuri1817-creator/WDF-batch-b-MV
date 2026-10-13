
let allEvents = [];
let filteredEvents = [];
let currentPage = 1;

const eventsPerPage = 3;

const eventContainer = document.getElementById("eventContainer");
const searchInput = document.getElementById("searchInput");
const locationFilter = document.getElementById("locationFilter");
const sortSelect = document.getElementById("sortSelect");
const previousButton = document.getElementById("previousButton");
const nextButton = document.getElementById("nextButton");
const pageInfo = document.getElementById("pageInfo");
const noResults = document.getElementById("noResults");
const loadingMessage = document.getElementById("loadingMessage");

// Load events from MySQL through PHP.
fetch("fetch_events.php")
    .then(response => {
        if (!response.ok) {
            throw new Error("Failed to load events.");
        }

        return response.json();
    })
    .then(data => {
        if (!Array.isArray(data)) {
            throw new Error(
                data.error || "Invalid event data received."
            );
        }

        allEvents = data;
        filteredEvents = [...allEvents];

        createLocationOptions();
        applySorting();
    })
    .catch(error => {
        console.error("Error loading events:", error);

        loadingMessage.textContent =
            "Unable to load events. Please check your database connection.";

        eventContainer.innerHTML = "";
        noResults.style.display = "none";
    });

// Create location options from database records.
function createLocationOptions() {
    locationFilter.innerHTML =
        '<option value="all">All Locations</option>';

    const locations = [
        ...new Set(
            allEvents.map(event => event.location).filter(Boolean)
        )
    ];

    locations.sort();

    locations.forEach(location => {
        const option = document.createElement("option");

        option.value = location;
        option.textContent = location;

        locationFilter.appendChild(option);
    });
}

// Display events on the current page.
function displayEvents() {
    loadingMessage.style.display = "none";
    eventContainer.innerHTML = "";
    noResults.style.display = "none";

    if (filteredEvents.length === 0) {
        noResults.style.display = "block";
        previousButton.disabled = true;
        nextButton.disabled = true;
        pageInfo.innerHTML = "";
        pageInfo.textContent = "No pages";
        return;
    }

    const totalPages = Math.ceil(
        filteredEvents.length / eventsPerPage
    );

    // Keep the current page within the valid range.
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    const startIndex = (currentPage - 1) * eventsPerPage;
    const endIndex = startIndex + eventsPerPage;

    const eventsToDisplay = filteredEvents.slice(
        startIndex,
        endIndex
    );

    eventsToDisplay.forEach(event => {
        const card = document.createElement("div");
        card.className = "event-card";

        const image = document.createElement("img");
        image.src = event.image || "";
        image.alt = event.title || "Event image";

        if (!event.image) {
            image.style.display = "none";
        }

        const title = document.createElement("h3");
        title.textContent = event.title || "Untitled Event";

        const date = document.createElement("p");
        date.textContent = "Date: " + formatDate(event.date);

        const location = document.createElement("p");
        location.textContent =
            "Location: " + (event.location || "Not specified");

        const category = document.createElement("p");
        category.textContent =
            "Category: " + (event.category || "Not specified");

        const registerButton = document.createElement("button");
        registerButton.textContent = "Register";

        registerButton.addEventListener("click", function () {
            registerEvent(event.title);
        });

        card.appendChild(image);
        card.appendChild(title);
        card.appendChild(date);
        card.appendChild(location);
        card.appendChild(category);
        card.appendChild(registerButton);

        eventContainer.appendChild(card);
    });

    updatePagination();
}

// Format the event date.
function formatDate(dateString) {
    if (!dateString) {
        return "Not specified";
    }

    const date = new Date(dateString + "T00:00:00");

    if (isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric"
    });
}

// Search events.
searchInput.addEventListener("input", function () {
    applyFilters();
});

// Filter events by location.
locationFilter.addEventListener("change", function () {
    applyFilters();
});

// Apply search and location filters together.
function applyFilters() {
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedLocation = locationFilter.value;

    filteredEvents = allEvents.filter(event => {
        const title = (event.title || "").toLowerCase();
        const category = (event.category || "").toLowerCase();
        const location = (event.location || "").toLowerCase();

        const matchesSearch =
            title.includes(searchText) ||
            category.includes(searchText) ||
            location.includes(searchText);

        const matchesLocation =
            selectedLocation === "all" ||
            event.location === selectedLocation;

        return matchesSearch && matchesLocation;
    });

    currentPage = 1;
    applySorting();
}

// Sort events.
sortSelect.addEventListener("change", function () {
    currentPage = 1;
    applySorting();
});

function applySorting() {
    const sortValue = sortSelect.value;

    if (sortValue === "nameAsc") {
        filteredEvents.sort((a, b) =>
            (a.title || "").localeCompare(b.title || "")
        );
    } else if (sortValue === "nameDesc") {
        filteredEvents.sort((a, b) =>
            (b.title || "").localeCompare(a.title || "")
        );
    } else if (sortValue === "dateAsc") {
        filteredEvents.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );
    } else if (sortValue === "dateDesc") {
        filteredEvents.sort((a, b) =>
            new Date(b.date) - new Date(a.date)
        );
    }

    displayEvents();
}

// Create clickable numbered pagination buttons.
function updatePagination() {
    const totalPages = Math.ceil(
        filteredEvents.length / eventsPerPage
    );

    pageInfo.innerHTML = "";

    if (totalPages === 0) {
        pageInfo.textContent = "No pages";
        previousButton.disabled = true;
        nextButton.disabled = true;
        return;
    }

    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement("button");

        pageButton.textContent = i;
        pageButton.style.padding = "10px 15px";
        pageButton.style.margin = "0 4px";
        pageButton.style.border = "1px solid #11666f";
        pageButton.style.borderRadius = "5px";
        pageButton.style.cursor = "pointer";
        pageButton.style.fontWeight = "bold";

        if (i === currentPage) {
            pageButton.style.backgroundColor = "#11666f";
            pageButton.style.color = "white";
        } else {
            pageButton.style.backgroundColor = "white";
            pageButton.style.color = "#11666f";
        }

        pageButton.addEventListener("click", function () {
            currentPage = i;
            displayEvents();
        });

        pageInfo.appendChild(pageButton);
    }

    previousButton.disabled = currentPage === 1;
    nextButton.disabled = currentPage === totalPages;
}

// Previous page.
previousButton.addEventListener("click", function () {
    if (currentPage > 1) {
        currentPage--;
        displayEvents();
    }
});

// Next page.
nextButton.addEventListener("click", function () {
    const totalPages = Math.ceil(
        filteredEvents.length / eventsPerPage
    );

    if (currentPage < totalPages) {
        currentPage++;
        displayEvents();
    }
});

// Open event registration page.
function registerEvent(eventTitle) {
    window.location.href =
        "event_registration.php?event=" +
        encodeURIComponent(eventTitle);
}
