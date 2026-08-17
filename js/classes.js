import { maleClasses, femaleClasses } from "./data.js";

const defaultData = [...maleClasses, ...femaleClasses];


document.addEventListener("DOMContentLoaded", () => {
    let classes = JSON.parse(localStorage.getItem('classes')) || defaultData;
    if (!localStorage.getItem('classes')) {
        localStorage.setItem('classes', JSON.stringify(defaultData));
    }

    let currentDisplayData = [...classes];

    const tbody = document.getElementById("classes-body");
    const filtersDiv = document.getElementById("filters");

    filtersDiv.innerHTML = `
        <h3>Filter Classes</h3>
        <div class="filter-controls">
            <input type="text" id="searchBox" placeholder="Search Trainer...">
            
            <select id="dayBox">
                <option value="All">All Days</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
            </select>

            <select id="levelBox">
                <option value="All">All Levels</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
        </div>
    `;

    const render = (dataList) => {
        tbody.innerHTML = "";
        if (dataList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--accent);">No classes found.</td></tr>';
            return;
        }

        dataList.forEach(item => {
            const row = `
                <tr>
                    <td data-label="Class Name">${item.name}</td>
                    <td data-label="Trainer">${item.trainer}</td>
                    <td data-label="Day">${item.day}</td>
                    <td data-label="Time">${item.time}</td>
                    <td data-label="Duration">${item.duration} mins</td>
                    <td data-label="Level"><span class="badge ${item.difficulty.toLowerCase()}">${item.difficulty}</span></td>
                    <td data-label="Actions"><a href="./trainers.html" class="btn-details">Details</a></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    };

    const filterData = () => {
        const search = document.getElementById("searchBox").value.toLowerCase();
        const day = document.getElementById("dayBox").value;
        const level = document.getElementById("levelBox").value;

        currentDisplayData = classes.filter(c => {
            const matchSearch = c.trainer.toLowerCase().includes(search);
            const matchDay = (day === "All" || c.day === day);
            const matchLevel = (level === "All" || c.difficulty === level);
            return matchSearch && matchDay && matchLevel;
        });

        render(currentDisplayData);
    };

    const setupSorting = () => {
        const sortDirection = {};
        const headers = document.querySelectorAll("th");
        const fields = ["name", "trainer", "day", "time", "duration", "difficulty", null];

        headers.forEach((th, index) => {
            if (fields[index]) {
                th.style.cursor = "pointer";
                th.addEventListener("click", () => {
                    const field = fields[index];
                    sortDirection[field] = !sortDirection[field];

                    currentDisplayData.sort((a, b) => {
                        let valA = a[field];
                        let valB = b[field];
                        if (field === "duration") {
                            valA = parseInt(valA);
                            valB = parseInt(valB);
                        }

                        if (valA < valB)
                            return sortDirection[field] ? -1 : 1;

                        if (valA > valB)
                            return sortDirection[field] ? 1 : -1;

                        return 0;
                    });

                    render(currentDisplayData);
                });
            }
        });
    };

    document.getElementById("searchBox").addEventListener("input", filterData);
    document.getElementById("dayBox").addEventListener("change", filterData);
    document.getElementById("levelBox").addEventListener("change", filterData);

    render(classes);
    setupSorting();
});