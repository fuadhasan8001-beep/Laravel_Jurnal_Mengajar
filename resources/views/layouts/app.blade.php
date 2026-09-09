<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >
        <title>@yield('title', 'Jurnal Guru') | Jurnal Guru</title>
        <style>
            :root {
                --ink: #15213b;
                --muted: #71819d;
                --blue: #2864e8;
                --navy: #14264a;
                --pale: #f4f7fb;
                --line: #e5ebf3;
                --green: #11865b;
                --amber: #b16b00;
                --red: #c43b45;
                font-family: "Trebuchet MS", Arial, sans-serif;
                color: var(--ink);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--pale);
            }

            a {
                color: var(--blue);
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            .app-shell {
                display: flex;
                min-height: 100vh;
            }

            .sidebar {
                position: fixed;
                z-index: 20;
                display: flex;
                width: 250px;
                height: 100vh;
                flex-direction: column;
                padding: 28px 18px;
                background: var(--navy);
                color: #fff;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 11px;
                padding: 0 12px 32px;
                color: #fff;
                font-size: 20px;
                font-weight: 700;
            }

            .brand-mark {
                display: flex;
                width: 39px;
                height: 39px;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: var(--blue);
            }

            .nav-label {
                padding: 0 12px 10px;
                color: #8fa4cc;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .nav-list {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .nav-link {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: 9px;
                color: #c7d3e9;
                font-size: 14px;
                font-weight: 600;
            }

            .nav-link:hover,
            .nav-link.active {
                background: #223b6d;
                color: #fff;
                text-decoration: none;
            }

            .nav-link svg {
                width: 19px;
                height: 19px;
                flex: none;
            }

            .sidebar-footer {
                margin-top: auto;
                border-top: 1px solid #29416e;
                padding: 18px 10px 0;
            }

            .user-mini {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #dbe5f5;
            }

            .avatar {
                display: flex;
                width: 35px;
                height: 35px;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #dce9ff;
                color: var(--blue);
                font-weight: 700;
            }

            .user-mini strong {
                display: block;
                max-width: 140px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 13px;
            }

            .user-mini small {
                color: #91a5c8;
                font-size: 11px;
                text-transform: capitalize;
            }

            .logout {
                width: 100%;
                margin-top: 16px;
                border: 1px solid #385482;
                border-radius: 8px;
                padding: 9px;
                background: transparent;
                color: #c7d3e9;
                cursor: pointer;
                font-size: 12px;
                font-weight: 700;
            }

            .logout:hover {
                border-color: #6f91cc;
                color: #fff;
            }

            .main {
                width: calc(100% - 250px);
                margin-left: 250px;
            }

            .topbar {
                display: flex;
                min-height: 78px;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid var(--line);
                background: #fff;
                padding: 0 42px;
            }

            .eyebrow {
                color: var(--muted);
                font-size: 12px;
            }

            .topbar-title {
                margin: 3px 0 0;
                font-size: 18px;
            }

            .date-chip {
                display: flex;
                align-items: center;
                gap: 8px;
                color: var(--muted);
                font-size: 13px;
            }

            .notification-menu {
                position: relative;
            }

            .notification-button {
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 8px 10px;
                background: #fff;
                color: var(--ink);
                cursor: pointer;
                font-size: 12px;
                font-weight: 700;
            }

            .notification-list {
                position: absolute;
                z-index: 30;
                top: calc(100% + 8px);
                right: 0;
                width: 300px;
                border: 1px solid var(--line);
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 12px 30px #203b6420;
                padding: 8px;
            }

            .notification-item {
                display: block;
                border-radius: 7px;
                padding: 10px;
                color: var(--ink);
                font-size: 12px;
            }

            .notification-item:hover {
                background: var(--pale);
                text-decoration: none;
            }

            .notification-empty {
                padding: 12px;
                color: var(--muted);
                font-size: 12px;
            }

            .content {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 38px 42px 56px;
}

            .page-head {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 28px;
            }

            .page-head h1 {
                margin: 0;
                font-size: 29px;
                letter-spacing: -.03em;
            }

            .page-head p {
                margin: 7px 0 0;
                color: var(--muted);
                font-size: 14px;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 0;
                border-radius: 8px;
                padding: 11px 16px;
                background: var(--blue);
                color: #fff;
                cursor: pointer;
                font-size: 13px;
                font-weight: 700;
                box-shadow: 0 5px 12px #2864e82b;
            }

            .btn:hover {
                background: #1f55cd;
                color: #fff;
                text-decoration: none;
            }

            .btn-muted {
                border: 1px solid var(--line);
                background: #fff;
                color: var(--ink);
                box-shadow: none;
            }

            .btn-muted:hover {
                background: #f7f9fc;
                color: var(--ink);
            }

            .stats {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
                margin-bottom: 24px;
            }

            .stat-card,
            .panel {
                border: 1px solid var(--line);
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 8px 24px #203b6410;
            }

            .stat-card {
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 18px;
            }

            .stat-icon {
                display: flex;
                width: 42px;
                height: 42px;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: #eaf1ff;
                color: var(--blue);
            }

            .stat-icon.green {
                background: #e8f8f0;
                color: var(--green);
            }

            .stat-icon.amber {
                background: #fff4df;
                color: var(--amber);
            }

            .stat-icon.red {
                background: #fff0f1;
                color: var(--red);
            }

            .stat-card small {
                display: block;
                color: var(--muted);
                font-size: 12px;
            }

            .stat-card strong {
                display: block;
                margin-top: 4px;
                font-size: 23px;
            }

            .panel-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                border-bottom: 1px solid var(--line);
                padding: 18px 20px;
            }

            .panel-head h2,
            .panel-head h3 {
                margin: 0;
                font-size: 16px;
            }

            .panel-body {
                padding: 20px;
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
            }

            th {
                background: #f8fafd;
                color: #71819d;
                font-size: 11px;
                letter-spacing: .04em;
                text-align: left;
                text-transform: uppercase;
            }

            th,
            td {
                padding: 14px 16px;
                border-bottom: 1px solid var(--line);
                white-space: nowrap;
            }

            tbody tr:last-child td {
                border-bottom: 0;
            }

            tbody tr:hover {
                background: #fbfcff;
            }

            .status {
                display: inline-flex;
                border-radius: 99px;
                padding: 5px 9px;
                font-size: 11px;
                font-weight: 700;
            }

            .status.pending {
                background: #fff4df;
                color: var(--amber);
            }

            .status.approved {
                background: #e8f8f0;
                color: var(--green);
            }

            .status.rejected {
                background: #fff0f1;
                color: var(--red);
            }

            .empty {
                padding: 38px 20px;
                color: var(--muted);
                text-align: center;
            }

            .alert {
                margin-bottom: 18px;
                border-radius: 9px;
                padding: 12px 15px;
                font-size: 13px;
            }

            .alert.success {
                border: 1px solid #b9e7d0;
                background: #edfbf4;
                color: #126b4c;
            }

            .alert.error {
                border: 1px solid #f4c5ca;
                background: #fff1f2;
                color: #a42e39;
            }

            .form-panel {
    width: 100%;
    max-width: none;
}

            .form-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }

            .field {
                display: flex;
                flex-direction: column;
                gap: 7px;
            }

            .field.full {
                grid-column: 1/-1;
            }

            .field label {
                color: #4b5c75;
                font-size: 13px;
                font-weight: 700;
            }

            .field input,
            .field select,
            .field textarea {
                width: 100%;
                border: 1px solid #dce4ef;
                border-radius: 8px;
                padding: 11px 12px;
                outline: 0;
                background: #fff;
                color: var(--ink);
                font-size: 14px;
            }

            .field textarea {
                min-height: 120px;
                resize: vertical;
            }

            .field input:focus,
            .field select:focus,
            .field textarea:focus {
                border-color: var(--blue);
                box-shadow: 0 0 0 4px #dceaff;
            }

            .field-readonly {
                border: 1px solid #dce4ef;
                border-radius: 8px;
                padding: 11px 12px;
                background: #f7f9fc;
                color: var(--ink);
                font-size: 14px;
                font-weight: 700;
            }

            .field-help {
                color: var(--muted);
                font-size: 11px;
                font-weight: 400;
            }

            .attendance-section {
                margin-top: 28px;
                border: 1px solid var(--line);
                border-radius: 10px;
                overflow: hidden;
            }

            .attendance-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 16px;
                background: #f7f9fc;
            }

            .attendance-head h3,
            .attendance-head p {
                margin: 0;
            }

            .attendance-head h3 {
                font-size: 15px;
            }

            .attendance-head p {
                margin-top: 4px;
                color: var(--muted);
                font-size: 12px;
            }

            .attendance-section select,
            .attendance-section input {
                min-width: 120px;
            }

            .attendance-empty {
                margin-top: 28px;
            }

            .signature-space {
                display: flex;
                justify-content: flex-end;
                margin-top: 32px;
            }

            .signature-space > div {
                display: flex;
                width: 230px;
                flex-direction: column;
                gap: 6px;
                text-align: center;
                font-size: 12px;
            }

            .signature-line {
                min-height: 92px;
                justify-content: flex-end;
                border-bottom: 1px solid var(--ink);
                color: var(--muted);
            }

            .form-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 24px;
            }

            .detail-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0 32px;
            }

            .detail-item {
                border-bottom: 1px solid var(--line);
                padding: 15px 0;
            }

            .detail-item dt {
                color: var(--muted);
                font-size: 12px;
            }

            .detail-item dd {
                margin: 5px 0 0;
                font-size: 14px;
                font-weight: 600;
            }

            .quick-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .quick-card {
                display: flex;
                flex-direction: column;
                gap: 7px;
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 18px;
                background: #fbfcff;
            }

            .quick-card:hover {
                border-color: #bfd1f5;
                background: #f5f8ff;
                text-decoration: none;
            }

            .quick-card strong {
                color: var(--ink);
                font-size: 14px;
            }

            .quick-card span {
                color: var(--muted);
                font-size: 12px;
            }

            @media (max-width:900px) {
                .sidebar {
                    width: 220px;
                }

                .main {
                    width: calc(100% - 220px);
                    margin-left: 220px;
                }

                .topbar,
                .content {
                    padding-left: 24px;
                    padding-right: 24px;
                }

                .stats {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                }
            }

            @media (max-width:680px) {
                .sidebar {
                    position: static;
                    width: 100%;
                    height: auto;
                    padding: 16px;
                }

                .app-shell {
                    display: block;
                }

                .brand {
                    padding: 0 8px 16px;
                }

                .nav-label,
                .nav-list,
                .sidebar-footer {
                    display: none;
                }

                .main {
                    width: 100%;
                    margin-left: 0;
                }

                .topbar {
                    min-height: 70px;
                    padding: 0 18px;
                }

                .date-chip {
                    display: none;
                }

                .content {
                    padding: 26px 16px 40px;
                }

                .page-head {
                    align-items: flex-start;
                    flex-direction: column;
                    margin-bottom: 22px;
                }

                .page-head h1 {
                    font-size: 25px;
                }

                .stats {
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                }

                .stat-card {
                    padding: 14px 12px;
                }

                .stat-card strong {
                    font-size: 20px;
                }

                .form-grid,
                .detail-grid,
                .quick-grid {
                    grid-template-columns: 1fr;
                }
            }
/* =========================================================
   JOURNAL - RESPONSIVE FIX
   ========================================================= */

.journal-layout {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 24px;
    align-items: start;
}

.journal-main {
    min-width: 0;
}

.journal-side {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.journal-card {
    width: 100%;
    box-sizing: border-box;
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    overflow: hidden;
}

.journal-card-header {
    padding: 18px 20px;
    border-bottom: 1px solid #e5eaf1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.journal-card-header h3 {
    margin: 0;
    font-size: 16px;
}

.journal-card-header p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #8290a5;
}

.journal-main > .journal-card:first-child {
    padding-bottom: 22px;
}

.journal-main > .journal-card:first-child .form-grid,
.journal-main > .journal-card:first-child > .field {
    margin-left: 20px;
    margin-right: 20px;
}

.journal-main > .journal-card:first-child .form-grid {
    margin-top: 20px;
}

.journal-main > .journal-card:first-child > .field {
    margin-top: 16px;
}

.form-grid {
    width: 100%;
    box-sizing: border-box;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field {
    min-width: 0;
}

.field label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: 600;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.field textarea {
    resize: vertical;
    min-height: 100px;
}


/* =========================================================
   ABSENSI
   ========================================================= */

.attendance-card {
    margin-top: 24px;
}

.attendance-card .table-wrap {
    width: 100%;
    overflow-x: auto;
}

.attendance-card table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
}

.attendance-card th,
.attendance-card td {
    padding: 12px 16px;
    text-align: left;
    vertical-align: middle;
}

.attendance-card input,
.attendance-card select {
    max-width: 100%;
    box-sizing: border-box;
}

.attendance-card td:last-child {
    width: 35%;
}


/* =========================================================
   SIDEBAR / INFO
   ========================================================= */

.journal-info {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.journal-info > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.journal-info span {
    font-size: 11px;
    color: #8794a8;
}

.journal-info strong {
    font-size: 14px;
    color: #172033;
    word-break: break-word;
}


/* =========================================================
   TANDA TANGAN
   ========================================================= */

.signature-card {
    overflow: hidden;
}

.signature-space {
    padding: 24px 20px;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 20px;
}

.signature-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.signature-info strong {
    font-size: 14px;
}

.signature-info span {
    font-size: 11px;
    color: #8794a8;
}

.signature-box {
    min-height: 90px;
    border: 1px dashed #cbd6e5;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 15px;
    font-size: 11px;
    color: #8794a8;
}


/* =========================================================
   BUTTON
   ========================================================= */

.form-actions {
    width: 100%;
    max-width: 1200px;
    margin: 24px auto 0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 1000px) {

    .journal-layout {
        grid-template-columns: minmax(0, 1fr);
        max-width: 800px;
    }

    .journal-side {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .form-actions {
        max-width: 800px;
    }
}


/* =========================================================
   HP
   ========================================================= */

@media (max-width: 640px) {

    .journal-layout {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: none;
        gap: 16px;
    }

    .journal-main,
    .journal-side {
        width: 100%;
    }

    .journal-side {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .journal-card-header {
        padding: 15px;
    }

    .journal-card-header h3 {
        font-size: 14px;
    }

    .journal-card-header p {
        font-size: 11px;
    }

    .journal-main > .journal-card:first-child .form-grid,
    .journal-main > .journal-card:first-child > .field {
        margin-left: 15px;
        margin-right: 15px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .field label {
        font-size: 12px;
    }

    .field input,
    .field select,
    .field textarea {
        font-size: 13px;
    }

    .attendance-card {
        margin-top: 16px;
    }

    .attendance-card .journal-card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .attendance-card .journal-card-header .btn {
        width: 100%;
    }

    .form-actions {
        width: 100%;
        max-width: none;
        padding: 0;
        margin-top: 16px;
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }

    .signature-space {
        min-height: 130px;
    }
}


/* =========================================================
   HP KECIL
   ========================================================= */

@media (max-width: 400px) {

    .journal-card-header {
        padding: 13px;
    }

    .journal-main > .journal-card:first-child .form-grid,
    .journal-main > .journal-card:first-child > .field {
        margin-left: 13px;
        margin-right: 13px;
    }

    .journal-info,
    .signature-space {
        padding-left: 13px;
        padding-right: 13px;
    }

    .attendance-card th,
    .attendance-card td {
        padding: 9px 10px;
    }

    /* =========================================================
   JOURNAL - FULL WIDTH DESKTOP + RESPONSIVE
========================================================= */

.form-panel:has(.journal-layout) {
    width: 100%;
    max-width: none;
}

.journal-layout {
    width: 100%;
    max-width: none;
    margin: 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: start;
}

.journal-main {
    min-width: 0;
}

.journal-side {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* tombol bawah juga full area */
.form-actions {
    width: 100%;
    max-width: none;
    margin: 24px 0 0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1000px) {

    .journal-layout {
        grid-template-columns: 1fr;
    }

    .journal-side {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}


/* =========================================================
   HP
========================================================= */

@media (max-width: 680px) {

    .form-panel:has(.journal-layout) {
        width: 100%;
        max-width: 100%;
    }

    .journal-layout {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .journal-main,
    .journal-side {
        width: 100%;
    }

    .journal-side {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }

}


/* =========================================================
   HP KECIL
========================================================= */

@media (max-width: 400px) {

    .content {
        padding-left: 14px;
        padding-right: 14px;
    }


}

}

        </style>
    </head>

    <body>
        @auth
            <div class="app-shell">
                <aside class="sidebar">
                    <a
                        href="{{ url('/' . auth()->user()->role) }}"
                        class="brand"
                    ><span class="brand-mark"><svg
                                width="22"
                                height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                                <path
                                    d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"
                                />
                            </svg></span>Jurnal Guru</a>
                    <div class="nav-label">Menu utama</div>
                    <nav class="nav-list">
                        <a
                            class="nav-link {{ request()->is(auth()->user()->role) ? 'active' : '' }}"
                            href="{{ url('/' . auth()->user()->role) }}"
                        ><svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9Z" />
                                <path d="M9 21v-6h6v6" />
                            </svg>Dashboard</a>
                        @if (in_array(auth()->user()->role, ['admin', 'guru', 'piket'], true))
                            <a
                                class="nav-link {{ request()->is('absensi*') ? 'active' : '' }}"
                                href="{{ route('absensi.index') }}"
                            ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="4"
                                        y="3"
                                        width="16"
                                        height="18"
                                        rx="2"
                                    />
                                    <path d="M8 8h8M8 12h8M8 16h5" />
                                </svg>Kelola absensi</a>
                        @endif
                        @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))
                            <a
                                class="nav-link {{ request()->is('jurnal*') ? 'active' : '' }}"
                                href="{{ route('jurnal.index') }}"
                            ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M5 4h14v16H5z" />
                                    <path d="M8 8h8M8 12h8M8 16h5" />
                                </svg>Jurnal mengajar</a>
                        @endif
                        @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris', 'piket'], true))
                            <a
                                class="nav-link {{ request()->is('rekap*') ? 'active' : '' }}"
                                href="{{ route('laporan.jurnal') }}"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M4 5h16v14H4z" />
                                    <path d="M8 9h8M8 13h5M8 17h4" />
                                </svg>
                                Rekap laporan
                            </a>
                        @endif
                        @if (auth()->user()->role === 'admin')
                            <a
                                class="nav-link {{ request()->is('admin/registrations*') ? 'active' : '' }}"
                                href="{{ route('admin.registrations.index') }}"
                            >Pendaftaran</a>
                            <a
                                class="nav-link {{ request()->is('admin/data*') ? 'active' : '' }}"
                                href="{{ route('admin.gurus.index') }}"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20a6 6 0 0 1 12 0M17 11v6M14 14h6" />
                                </svg>
                                Data master
                            </a>
                            <a
                                class="nav-link {{ request()->is('admin/data/siswa*') ? 'active' : '' }}"
                                href="{{ route('admin.siswas.index') }}"
                            >Siswa</a>
                            <a
                                class="nav-link {{ request()->is('admin/data/kelas*') ? 'active' : '' }}"
                                href="{{ route('admin.kelas.index') }}"
                            >Kelas</a>
                            <a
                                class="nav-link {{ request()->is('admin/data/mapel*') ? 'active' : '' }}"
                                href="{{ route('admin.mapel.index') }}"
                            >Mata pelajaran</a>
                            <a
                                class="nav-link {{ request()->is('admin/data/jam*') ? 'active' : '' }}"
                                href="{{ route('admin.jam.index') }}"
                            >Jam pelajaran</a>
                        @endif
                        @if (in_array(auth()->user()->role, ['siswa', 'piket', 'admin'], true))
                            <a
                                class="nav-link {{ request()->is('dispensasi*') ? 'active' : '' }}"
                                href="{{ route('dispensasi.index') }}"
                            ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M6 3h12a2 2 0 0 1 2 2v16l-8-4-8 4V5a2 2 0 0 1 2-2Z" />
                                    <path d="M8 8h8M8 12h5" />
                                </svg>Dispensasi</a>
                        @endif
                    </nav>
                    <div class="sidebar-footer">
                        <div class="user-mini"><span
                                class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role }}</small></span>
                        </div>
                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >@csrf<button
                                class="logout"
                                type="submit"
                            >Keluar dari akun</button></form>
                    </div>
                </aside>
                <div class="main">
                    <header class="topbar">
                        <div>
                            <div class="eyebrow">Ruang kerja digital</div>
                            <div class="topbar-title">Jurnal Guru</div>
                        </div>
                        <div class="date-chip">
                            <div class="notification-menu">
                                <details>
                                    <summary class="notification-button">
                                        Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})
                                    </summary>
                                    <div class="notification-list">
                                        @forelse (auth()->user()->unreadNotifications()->latest()->limit(5)->get() as $notification)
                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                @csrf
                                                <button class="notification-item" type="submit">
                                                    {{ $notification->data['message'] ?? 'Ada notifikasi baru.' }}
                                                </button>
                                            </form>
                                        @empty
                                            <div class="notification-empty">Tidak ada notifikasi baru.</div>
                                        @endforelse
                                        @if (auth()->user()->unreadNotifications()->exists())
                                            <form action="{{ route('notifications.read-all') }}" method="POST">
                                                @csrf
                                                <button class="notification-button" type="submit">Tandai semua dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            </div>
                            <svg
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="17"
                                    rx="2"
                                />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>{{ now()->translatedFormat('l, d F Y') }}
                        </div>
                    </header>
                    <main class="content">
                        @if (session('success'))
                            <div class="alert success">{{ session('success') }}</div>
                            @endif @if ($errors->any())
                                <div class="alert error">{{ $errors->first() }}</div>
                            @endif @yield('content')
                    </main>
                </div>
                </div>
            </div>
        @else
            @yield('content')
        @endauth
</body>

</html>
