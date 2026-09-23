let allEvents = [];
let filteredEvents = [];
let currentPage = 1;
const eventsPerPage = 3;
fetch("events.json")
    .then(response => {
        if (!response.ok) {
            throw new Error("Failed to load events.json");
        }
        return response.json();
    })
    .then(data => {
        allEvents = data;
        filteredEvents = [...allEvents];
        createLocationOptions();
        displayEvents();
    })
    .catch(error => {
        console.log("Error:", error);
        document.getElementById("loadingMessage").textContent =
            "Unable to load events. Please try again.";
    });
const eventContainer =document.getElementById("eventContainer");
const searchInput =document.getElementById("searchInput");
const locationFilter =document.getElementById("locationFilter");
const sortSelect =document.getElementById("sortSelect");
const previousButton =document.getElementById("previousButton");
const nextButton =document.getElementById("nextButton");
const pageInfo =document.getElementById("pageInfo");
const noResults =document.getElementById("noResults");
const loadingMessage =document.getElementById("loadingMessage");

function createLocationOptions() {
    const locations = [...new Set(allEvents.map(event => event.location))];
    locations.forEach(location => {
        const option = document.createElement("option");
        option.value = location;
        option.textContent = location;
        locationFilter.appendChild(option);
    });
}

function displayEvents() {
    loadingMessage.style.display = "none";
    eventContainer.innerHTML = "";
    noResults.style.display = "none";
    if (filteredEvents.length === 0) {
        noResults.style.display = "block";
        previousButton.disabled = true;
        nextButton.disabled = true;
        pageInfo.textContent = "No pages";
        return;
    }
    const startIndex =(currentPage - 1) * eventsPerPage;
    const endIndex =startIndex + eventsPerPage;
    const eventsToDisplay =filteredEvents.slice(startIndex, endIndex);
    
    eventsToDisplay.forEach(event => {
        const card = document.createElement("div");
        card.className = "event-card";
        card.innerHTML = `
            <img src="${event.image}" alt="${event.title}">
            <h3>${event.title}</h3>
            <p>Date: ${formatDate(event.date)}</p>
            <p>Location: ${event.location}</p>
            <p>Category: ${event.category}</p>
            <button onclick="registerEvent('${event.title.replace(/'/g, "\\'")}')">
                Register
            </button>
        `;
        eventContainer.appendChild(card);
    });
    updatePagination();
}
function formatDate(dateString) {
    const date = new Date(dateString + "T00:00:00");
    return date.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric"
    });
}
searchInput.addEventListener("input", function() {
    const searchText =
        searchInput.value.toLowerCase().trim();
    filteredEvents = allEvents.filter(event => {
        return (
            event.title.toLowerCase().includes(searchText)
            ||
            event.category.toLowerCase().includes(searchText)
            ||
            event.location.toLowerCase().includes(searchText)
        );
    });
    applyLocationFilter();
});
locationFilter.addEventListener("change", function() {
    applyLocationFilter();
});
function applyLocationFilter() {
    const searchText =
        searchInput.value.toLowerCase().trim();
    const selectedLocation =
        locationFilter.value;
    filteredEvents = allEvents.filter(event => {
        const matchesSearch =
            event.title.toLowerCase().includes(searchText)
            ||
            event.category.toLowerCase().includes(searchText)
            ||
            event.location.toLowerCase().includes(searchText);
        const matchesLocation =
            selectedLocation === "all"
            ||
            event.location === selectedLocation;
        return matchesSearch && matchesLocation;
    });
    currentPage = 1;
    applySorting();
}
sortSelect.addEventListener("change", function() {
    currentPage = 1;
    applySorting();
});
function applySorting() {
    const sortValue = sortSelect.value;
    if (sortValue === "nameAsc") {
        filteredEvents.sort((a, b) =>
            a.title.localeCompare(b.title)
        );
    }
    else if (sortValue === "nameDesc") {
        filteredEvents.sort((a, b) =>
            b.title.localeCompare(a.title)
        );
    }
    else if (sortValue === "dateAsc") {
        filteredEvents.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );
    }
    else if (sortValue === "dateDesc") {
        filteredEvents.sort((a, b) =>
            new Date(b.date) - new Date(a.date)
        );
    }
    displayEvents();
}
function updatePagination() {
    const totalPages =
        Math.ceil(filteredEvents.length / eventsPerPage);
    pageInfo.innerHTML = "";
    if (totalPages === 0) {
        pageInfo.textContent = "No pages";
        previousButton.disabled = true;
        nextButton.disabled = true;
        return;
    }
    for (let i = 1; i <= totalPages; i++) {
        const pageButton =
            document.createElement("button");
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
        }
        else {
            pageButton.style.backgroundColor = "white";
            pageButton.style.color = "#11666f";
        }
        pageButton.addEventListener("click", function() {
            currentPage = i;
            displayEvents();
        });
        pageInfo.appendChild(pageButton);
    }
    previousButton.disabled = currentPage === 1;
    nextButton.disabled = currentPage === totalPages;
}
previousButton.addEventListener("click", function() {
    if (currentPage > 1) {
        currentPage--;
        displayEvents();
    }
});
nextButton.addEventListener("click", function() {
    const totalPages =
        Math.ceil(filteredEvents.length / eventsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        displayEvents();
    }
});
function registerEvent(eventTitle) {

    alert("You selected: " + eventTitle);

}