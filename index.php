/* =========================================
   CHARITY HUB - MAIN DASHBOARD
   ========================================= */

/* General settings */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7f6;
    color: #263238;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
    width: 250px;
    min-width: 250px;
    background: #145c3b;
    color: white;
    padding: 25px 15px;
    display: flex;
    flex-direction: column;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 35px;
}

.logo-icon {
    font-size: 30px;
    color: #8ce0ad;
}

.menu-heading {
    color: #b7d9c6;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1px;
    margin: 20px 10px 12px;
}

.navigation {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 12px;
    color: #ecf6ef;
    text-decoration: none;
    border-radius: 7px;
    font-size: 14px;
    transition: background 0.2s;
}

.nav-link span {
    width: 23px;
    text-align: center;
}

.nav-link:hover,
.nav-link.active {
    background: #26794f;
    color: white;
}

.sidebar-footer {
    margin-top: auto;
    padding: 25px 10px 5px;
    color: #d5eadc;
    font-size: 13px;
}

.sidebar-footer p {
    font-weight: bold;
    margin-bottom: 5px;
}

.sidebar-footer small {
    font-size: 11px;
}

/* MAIN CONTENT */
.main-content {
    flex: 1;
    min-width: 0;
}

.topbar {
    min-height: 95px;
    background: white;
    padding: 20px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 1px solid #e6ece8;
}

.topbar h2 {
    margin: 0 0 6px;
    font-size: 23px;
    color: #245b40;
}

.welcome-text {
    margin: 0;
    color: #7b8780;
    font-size: 13px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #d9f1e2;
    color: #176b45;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
}

.admin-profile strong,
.admin-profile small {
    display: block;
}

.admin-profile strong {
    font-size: 13px;
}

.admin-profile small {
    color: #7b8780;
    margin-top: 4px;
    font-size: 11px;
}

.menu-toggle {
    display: none;
    background: #176b45;
    color: white;
    border: none;
    padding: 10px 13px;
    border-radius: 5px;
    cursor: pointer;
}

/* PAGE CONTENT */
.content {
    padding: 30px;
}

.page-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0 0 8px;
    color: #245b40;
    font-size: 27px;
}

.page-heading p {
    margin: 0;
    color: #7b8780;
    font-size: 14px;
}

.today-date {
    background: white;
    border: 1px solid #e0e9e3;
    padding: 12px 16px;
    border-radius: 7px;
    font-size: 13px;
    color: #52675a;
}

/* STATISTICS CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 22px;
    border: 1px solid #e8eee9;
    box-shadow: 0 3px 10px rgba(30, 70, 45, 0.04);
}

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.stat-title {
    color: #6d7c72;
    font-size: 13px;
}

.stat-icon {
    background: #e4f5e9;
    padding: 10px;
    border-radius: 8px;
    font-size: 21px;
}

.stat-card h2 {
    color: #176b45;
    font-size: 25px;
    margin: 20px 0 10px;
    overflow-wrap: anywhere;
}

.stat-card p {
    color: #849188;
    font-size: 12px;
    margin: 0;
}

/* LOWER DASHBOARD SECTIONS */
.dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(250px, 1fr);
    gap: 22px;
    align-items: start;
}

.panel {
    background: white;
    border: 1px solid #e7ede8;
    border-radius: 10px;
    padding: 22px;
    min-width: 0;
    box-shadow: 0 3px 10px rgba(30, 70, 45, 0.04);
}

.panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.panel-heading h3 {
    color: #245b40;
    font-size: 17px;
    margin: 0 0 7px;
}

.panel-heading p {
    color: #849188;
    font-size: 12px;
    margin: 0;
}

/* VIEW ALL BUTTON */
.view-button {
    background: #e5f4e9;
    color: #176b45;
    border: none;
    padding: 10px 14px;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}

.view-button:hover {
    background: #ccebd6;
}

/* DONATIONS TABLE */
.table-container {
    width: 100%;
    overflow-x: auto;
}

.table-container table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.table-container th {
    background: #f1f7f3;
    color: #64766a;
    font-size: 12px;
    font-weight: bold;
    padding: 13px 10px;
    white-space: nowrap;
}

.table-container td {
    padding: 15px 10px;
    border-bottom: 1px solid #edf1ee;
    font-size: 12px;
}

.table-container tr:last-child td {
    border-bottom: none;
}

/* DONATION STATUS */
.status {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
}

.status.completed {
    background: #dcf5e5;
    color: #197143;
}

.status.pending {
    background: #fff0cf;
    color: #966400;
}

/* RECENT ACTIVITIES */
.activity-list {
    display: flex;
    flex-direction: column;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 0;
    border-bottom: 1px solid #edf1ee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-dot {
    width: 10px;
    height: 10px;
    min-width: 10px;
    margin-top: 5px;
    background: #36a66b;
    border-radius: 50%;
}

.activity-item p {
    margin: 0;
    color: #59685e;
    font-size: 13px;
    line-height: 1.6;
}

/* FOOTER */
.footer {
    margin-top: 30px;
    padding: 20px 0;
    text-align: center;
    color: #89958d;
    font-size: 12px;
}

/* MOBILE AND TABLET DISPLAY */
@media (max-width: 1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .dashboard-container {
        display: block;
    }

    .sidebar {
        display: none;
        width: 100%;
        min-width: 0;
    }

    .sidebar.open {
        display: flex;
    }

    .menu-toggle {
        display: block;
    }

    .topbar {
        padding: 15px;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .topbar h2 {
        font-size: 19px;
    }

    .admin-profile {
        margin-left: auto;
    }

    .content {
        padding: 18px 12px;
    }

    .page-heading h1 {
        font-size: 23px;
    }

    .stats-grid {
        gap: 12px;
    }

    .stat-card {
        padding: 15px;
    }

    .stat-card h2 {
        font-size: 20px;
    }

    .panel {
        padding: 15px;
    }
}

@media (max-width: 380px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .admin-profile strong {
        font-size: 11px;
    }
}