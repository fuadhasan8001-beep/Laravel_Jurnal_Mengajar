<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

        /* =========================================================
           SIDEBAR
        ========================================================= */

        .sidebar {
            position: fixed;
            z-index: 50;
            top: 0;
            left: 0;

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

        .brand:hover {
            color: #fff;
            text-decoration: none;
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

            min-height: 42px;
            padding: 10px;

            border: 1px solid #385482;
            border-radius: 8px;

            background: transparent;
            color: #c7d3e9;

            cursor: pointer;

            font-size: 13px;
            font-weight: 700;
        }

        .logout:hover {
            border-color: #6f91cc;
            color: #fff;
        }

        /* =========================================================
           MAIN
        ========================================================= */

        .main {
            width: calc(100% - 250px);
            margin-left: 250px;
            min-width: 0;
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

        .menu-toggle {
            display: none;

            width: 38px;
            height: 38px;

            align-items: center;
            justify-content: center;

            border: 1px solid var(--line);
            border-radius: 8px;

            background: #fff;
            color: var(--ink);

            cursor: pointer;
        }

        /* =========================================================
           NOTIFICATION
        ========================================================= */

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
            z-index: 100;

            top: calc(100% + 8px);
            right: 0;

            width: 300px;

            border: 1px solid var(--line);
            border-radius: 10px;

            background: #fff;

            box-shadow: 0 12px 30px #203b6420;

            padding: 8px;

            max-width: calc(100vw - 32px);

            max-height: min(70vh, 420px);

            overflow-y: auto;
        }

        .notification-item {
            display: block;
            width: 100%;

            border: 0;
            border-radius: 7px;

            padding: 10px;

            background: transparent;
            color: var(--ink);

            text-align: left;
            cursor: pointer;

            font-size: 12px;
        }

        .notification-item:hover {
            background: var(--pale);
        }

        .notification-empty {
            padding: 12px;
            color: var(--muted);
            font-size: 12px;
        }

        .mobile-top-actions,
        .mobile-logout-button {
            display: none;
        }

        .contact-admin {
            display: block;

            margin-top: 10px;

            border: 1px solid #25d366;
            border-radius: 8px;

            padding: 9px 12px;

            background: #25d366;
            color: #073b1a;

            text-align: center;

            font-size: 12px;
            font-weight: 800;
        }

        .contact-admin:hover {
            background: #20bd5c;
            color: #073b1a;
            text-decoration: none;
        }

        /* =========================================================
           CONTENT
        ========================================================= */

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

        /* =========================================================
           BUTTON
        ========================================================= */

        .btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;
            gap: 8px;

            min-height: 44px;

            border: 0;
            border-radius: 8px;

            padding: 12px 18px;

            background: var(--blue);
            color: #fff;

            cursor: pointer;

            font-size: 14px;
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

        /* =========================================================
           STATS
        ========================================================= */

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

        /* =========================================================
           PANEL
        ========================================================= */

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

        /* =========================================================
           TABLE
        ========================================================= */

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

        /* =========================================================
           STATUS
        ========================================================= */

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

        /* =========================================================
           ALERT
        ========================================================= */

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

        .empty {
            padding: 38px 20px;

            color: var(--muted);

            text-align: center;
        }

        /* =========================================================
           FORM
        ========================================================= */

        .form-panel {
            width: 100%;
            max-width: none;
        }

        .form-grid {
            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 18px;
        }

        .field {
            display: flex;

            min-width: 0;

            flex-direction: column;

            gap: 7px;
        }

        .field.full {
            grid-column: 1 / -1;
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
            max-width: 100%;

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

        /* =========================================================
           JOURNAL
        ========================================================= */

        .form-panel:has(.journal-layout) {
            width: 100%;
            max-width: none;
        }

        .journal-layout {
            display: grid;

            width: 100%;
            max-width: none;

            margin: 0;

            grid-template-columns: minmax(0, 1fr) 340px;

            gap: 24px;

            align-items: start;
        }

        .journal-main,
        .journal-side {
            min-width: 0;
        }

        .journal-side {
            display: flex;
            flex-direction: column;

            gap: 24px;
        }

        .journal-card {
            width: 100%;

            border: 1px solid #dbe4f0;
            border-radius: 14px;

            background: #fff;

            overflow: hidden;
        }

        .journal-card-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 16px;

            border-bottom: 1px solid #e5eaf1;

            padding: 18px 20px;
        }

        .journal-card-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .journal-card-header p {
            margin: 4px 0 0;

            color: #8290a5;
            font-size: 12px;
        }

        .journal-main > .journal-card:first-child {
            padding-bottom: 22px;
        }

        .journal-main > .journal-card:first-child .form-grid,
        .journal-main > .journal-card:first-child > .field {
            margin-right: 20px;
            margin-left: 20px;
        }

        .journal-main > .journal-card:first-child .form-grid {
            margin-top: 20px;
        }

        .journal-main > .journal-card:first-child > .field {
            margin-top: 16px;
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
        }

        .attendance-card td:last-child {
            width: 35%;
        }

        /* =========================================================
           ATTENDANCE SECTION
        ========================================================= */

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

        /* =========================================================
           JOURNAL INFO
        ========================================================= */

        .journal-info {
            display: flex;

            flex-direction: column;

            gap: 16px;

            padding: 18px 20px;
        }

        .journal-info > div {
            display: flex;

            flex-direction: column;

            gap: 4px;
        }

        .journal-info span {
            color: #8794a8;
            font-size: 11px;
        }

        .journal-info strong {
            color: #172033;
            font-size: 14px;

            word-break: break-word;
        }

        /* =========================================================
           QUICK CARD
        ========================================================= */

        .quick-grid {
            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

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

        /* =========================================================
           SIGNATURE
        ========================================================= */

        .signature-card {
            overflow: hidden;
        }

        .signature-space {
            display: flex;

            min-height: 150px;

            flex-direction: column;
            justify-content: space-between;

            gap: 20px;

            padding: 24px 20px;
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
            color: #8794a8;
            font-size: 11px;
        }

        .signature-box {
            display: flex;

            min-height: 90px;

            align-items: center;
            justify-content: center;

            border: 1px dashed #cbd6e5;
            border-radius: 10px;

            padding: 15px;

            color: #8794a8;

            text-align: center;

            font-size: 11px;
        }

        .signature-canvas {
            width: 100%;
            min-height: 180px;

            border: 1px dashed #9eafc6;
            border-radius: 10px;

            background: #fff;

            cursor: crosshair;

            touch-action: none;
        }

        .saved-signature {
            display: block;

            width: min(100%, 420px);
            max-height: 180px;

            object-fit: contain;
            object-position: left center;
        }

        /* =========================================================
           FORM ACTION
        ========================================================= */

        .form-actions {
            display: flex;

            width: 100%;
            max-width: none;

            justify-content: flex-end;

            gap: 10px;

            margin: 24px 0 0;
        }

        /* =========================================================
           MOBILE NAV
        ========================================================= */

        .mobile-nav {
            display: none;
        }

        .sidebar-backdrop {
            display: none;
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

                gap: 16px;
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 220px;
            }

            .main {
                width: calc(100% - 220px);
                margin-left: 220px;
            }

            .topbar,
            .content {
                padding-right: 24px;
                padding-left: 24px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);

                gap: 12px;
            }
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 680px) {
            .sidebar {
                position: fixed;

                z-index: 50;

                top: 0;
                left: 0;

                width: min(82vw, 300px);
                height: 100vh;

                padding: 22px 18px;

                overflow-y: auto;

                background: var(--navy);

                transform: translateX(-105%);

                visibility: hidden;

                pointer-events: none;

                transition:
                    transform .2s ease,
                    visibility .2s ease;
            }

            .app-shell.menu-open .sidebar {
                transform: translateX(0);

                visibility: visible;

                pointer-events: auto;
            }

            .sidebar-backdrop {
                position: fixed;

                z-index: 40;

                inset: 0;

                background: #08142688;
            }

            .app-shell.menu-open .sidebar-backdrop {
                display: block;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .main {
                width: 100%;
                margin-left: 0;
            }

            .topbar {
                min-height: 78px;

                padding: 0 18px;
            }

            .topbar > div:first-child {
                min-width: 0;
            }

            .topbar > div:first-child {
                display: flex;

                align-items: center;

                gap: 10px;
            }

            .date-chip {
                display: none;
            }

            .mobile-top-actions {
                min-width: 0;

                display: flex;

                align-items: center;

                gap: 8px;
            }

            .mobile-logout-button {
                display: inline-flex;

                align-items: center;
                justify-content: center;

                border: 1px solid var(--line);
                border-radius: 8px;

                padding: 8px 10px;

                background: #fff;
                color: var(--ink);

                cursor: pointer;

                font-size: 11px;
                font-weight: 700;
            }

            .mobile-top-actions .notification-list {
                position: fixed;

                top: 76px;
                right: 16px;
                left: 16px;

                width: auto;

                max-width: none;
            }

            .mobile-notification-button {
                max-width: 42vw;

                overflow: hidden;

                text-overflow: ellipsis;

                white-space: nowrap;
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

            .journal-layout {
                display: flex;

                width: 100%;

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
                margin-right: 15px;
                margin-left: 15px;
            }

            .form-grid {
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
                align-items: flex-start;

                flex-direction: column;
            }

            .attendance-card .journal-card-header .btn {
                width: 100%;
            }

            .form-actions {
                flex-direction: column-reverse;

                margin-top: 16px;
            }

            .form-actions .btn {
                width: 100%;
            }

            .mobile-nav {
                display: flex;

                position: sticky;
                z-index: 30;

                top: 0;

                gap: 8px;

                overflow-x: auto;

                padding: 10px 16px;

                border-bottom: 1px solid var(--line);

                background: #fff;
            }

            .mobile-nav-link {
                flex: none;

                border: 1px solid var(--line);
                border-radius: 999px;

                padding: 8px 12px;

                background: #fff;
                color: var(--ink);

                font-size: 11px;
                font-weight: 700;

                white-space: nowrap;
            }

            .mobile-nav-link.active {
                border-color: var(--blue);

                background: var(--blue);
                color: #fff;
            }

            .mobile-nav-link:hover {
                text-decoration: none;
            }

            .signature-space {
                min-height: 130px;
            }
        }

        /* =========================================================
           HP KECIL
        ========================================================= */

        @media (max-width: 400px) {
            .content {
                padding-right: 14px;
                padding-left: 14px;
            }

            .journal-card-header {
                padding: 13px;
            }

            .journal-main > .journal-card:first-child .form-grid,
            .journal-main > .journal-card:first-child > .field {
                margin-right: 13px;
                margin-left: 13px;
            }

            .journal-info,
            .signature-space {
                padding-right: 13px;
                padding-left: 13px;
            }

            .attendance-card th,
            .attendance-card td {
                padding: 9px 10px;
            }
        }
    </style>
</head>

<body>

    @auth
        <div class="app-shell">

            <div class="sidebar-backdrop" data-menu-close></div>

            <aside class="sidebar">

                <a
                    href="{{ url('/' . auth()->user()->role) }}"
                    class="brand"
                >
                    <span class="brand-mark">
                        <svg
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                            <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" />
                        </svg>
                    </span>

                    Jurnal Guru
                </a>

                <div class="nav-label">Menu utama</div>

                <nav class="nav-list">

                    <a
                        class="nav-link {{ request()->is(auth()->user()->role) ? 'active' : '' }}"
                        href="{{ url('/' . auth()->user()->role) }}"
                    >
                        Dashboard
                    </a>

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'piket'], true))
                        <a
                            class="nav-link {{ request()->is('absensi*') ? 'active' : '' }}"
                            href="{{ route('absensi.index') }}"
                        >
                            Kelola absensi
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))
                        <a
                            class="nav-link {{ request()->is('jurnal*') ? 'active' : '' }}"
                            href="{{ route('jurnal.index') }}"
                        >
                            Jurnal mengajar
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris', 'piket'], true))
                        <a
                            class="nav-link {{ request()->is('rekap*') ? 'active' : '' }}"
                            href="{{ route('laporan.jurnal') }}"
                        >
                            Rekap laporan
                        </a>
                    @endif

                    @if (auth()->user()->role === 'admin')

                        <a
                            class="nav-link {{ request()->is('admin/registrations*') ? 'active' : '' }}"
                            href="{{ route('admin.registrations.index') }}"
                        >
                            Pendaftaran
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}"
                            href="{{ route('admin.activity-logs') }}"
                        >
                            Riwayat aktivitas
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data*') ? 'active' : '' }}"
                            href="{{ route('admin.gurus.index') }}"
                        >
                            Data master
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data/siswa*') ? 'active' : '' }}"
                            href="{{ route('admin.siswas.index') }}"
                        >
                            Siswa
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data/kelas*') ? 'active' : '' }}"
                            href="{{ route('admin.kelas.index') }}"
                        >
                            Kelas
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data/mapel*') ? 'active' : '' }}"
                            href="{{ route('admin.mapel.index') }}"
                        >
                            Mata pelajaran
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data/jam*') ? 'active' : '' }}"
                            href="{{ route('admin.jam.index') }}"
                        >
                            Jam pelajaran
                        </a>

                    @endif

                    @if (in_array(auth()->user()->role, ['siswa', 'piket', 'admin'], true))
                        <a
                            class="nav-link {{ request()->is('dispensasi*') ? 'active' : '' }}"
                            href="{{ route('dispensasi.index') }}"
                        >
                            Dispensasi
                        </a>
                    @endif

                    <a
                        class="nav-link {{ request()->is('profile') ? 'active' : '' }}"
                        href="{{ route('profile') }}"
                    >
                        Profil
                    </a>

                    @if (filled(config('app.admin_whatsapp')))
                        <a
                            class="contact-admin"
                            href="https://wa.me/{{ preg_replace('/\\D+/', '', config('app.admin_whatsapp')) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Hubungi Admin
                        </a>
                    @endif

                </nav>

                <div class="sidebar-footer">

                    <div class="user-mini">

                        <span class="avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>

                        <span>
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->role }}</small>
                        </span>

                    </div>

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            class="logout"
                            type="submit"
                        >
                            Keluar dari akun
                        </button>
                    </form>

                </div>

            </aside>

            <div class="main">

                <header class="topbar">

                    <div>

                        <button
                            class="menu-toggle"
                            type="button"
                            aria-label="Buka menu"
                            aria-expanded="false"
                            data-menu-toggle
                        >
                            ☰
                        </button>

                        <div class="eyebrow">
                            Ruang kerja digital
                        </div>

                        <div class="topbar-title">
                            Jurnal Guru
                        </div>

                    </div>

                    <div class="date-chip">

                        <div class="notification-menu">

                            <details>

                                <summary class="notification-button">Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})</summary>

                                <div class="notification-list">

                                    @forelse (auth()->user()->unreadNotifications()->latest()->limit(5)->get() as $notification)

                                        <form
                                            action="{{ route('notifications.read', $notification->id) }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                class="notification-item"
                                                type="submit"
                                            >
                                                {{ $notification->data['message'] ?? 'Ada notifikasi baru.' }}
                                            </button>

                                        </form>

                                    @empty

                                        <div class="notification-empty">
                                            Tidak ada notifikasi baru.
                                        </div>

                                    @endforelse

                                    @if (auth()->user()->unreadNotifications()->exists())

                                        <form
                                            action="{{ route('notifications.read-all') }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                class="notification-button"
                                                type="submit"
                                            >
                                                Tandai semua dibaca
                                            </button>

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
                        </svg>

                        <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                        <time
                            class="live-clock"
                            data-live-clock
                            data-server-time="{{ now()->getTimestampMs() }}"
                            data-timezone="{{ config('app.timezone') }}"
                            datetime="{{ now()->toIso8601String() }}"
                        >
                            {{ now()->format('H:i:s') }}
                        </time>

                    </div>

                    <div class="mobile-top-actions">

                        <time
                            class="live-clock mobile-live-clock"
                            data-live-clock
                            data-server-time="{{ now()->getTimestampMs() }}"
                            data-timezone="{{ config('app.timezone') }}"
                            datetime="{{ now()->toIso8601String() }}"
                        >
                            {{ now()->format('H:i:s') }}
                        </time>

                        <div class="notification-menu">

                            <details>

                                <summary class="notification-button mobile-notification-button">Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})</summary>

                                <div class="notification-list">

                                    @forelse (auth()->user()->notifications()->latest()->limit(20)->get() as $notification)

                                        <form
                                            action="{{ route('notifications.read', $notification->id) }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                class="notification-item"
                                                type="submit"
                                            >
                                                {{ $notification->data['message'] ?? 'Ada notifikasi baru.' }}

                                                <br>

                                                <small>
                                                    {{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}
                                                    ·
                                                    {{ $notification->created_at->format('d/m H:i') }}
                                                </small>
                                            </button>

                                        </form>

                                    @empty

                                        <div class="notification-empty">
                                            Belum ada notifikasi.
                                        </div>

                                    @endforelse

                                    @if (auth()->user()->unreadNotifications()->exists())

                                        <form
                                            action="{{ route('notifications.read-all') }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                class="notification-button"
                                                type="submit"
                                            >
                                                Tandai semua dibaca
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </details>

                        </div>

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                class="mobile-logout-button"
                                type="submit"
                            >
                                Keluar
                            </button>

                        </form>

                    </div>

                </header>

                <nav
                    class="mobile-nav"
                    aria-label="Navigasi utama"
                >

                    <a
                        class="mobile-nav-link {{ request()->is(auth()->user()->role) ? 'active' : '' }}"
                        href="{{ url('/' . auth()->user()->role) }}"
                    >
                        Beranda
                    </a>

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'piket', 'sekretaris'], true))
                        <a
                            class="mobile-nav-link {{ request()->is('absensi*') ? 'active' : '' }}"
                            href="{{ route('absensi.index') }}"
                        >
                            Absensi
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))
                        <a
                            class="mobile-nav-link {{ request()->is('jurnal*') ? 'active' : '' }}"
                            href="{{ route('jurnal.index') }}"
                        >
                            Jurnal
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris', 'piket'], true))
                        <a
                            class="mobile-nav-link {{ request()->is('rekap*') ? 'active' : '' }}"
                            href="{{ route('laporan.jurnal') }}"
                        >
                            Laporan
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['siswa', 'piket', 'admin'], true))
                        <a
                            class="mobile-nav-link {{ request()->is('dispensasi*') ? 'active' : '' }}"
                            href="{{ route('dispensasi.index') }}"
                        >
                            Dispensasi
                        </a>
                    @endif

                    @if (auth()->user()->role === 'admin')

                        <a
                            class="mobile-nav-link {{ request()->is('admin/registrations*') ? 'active' : '' }}"
                            href="{{ route('admin.registrations.index') }}"
                        >
                            Pendaftaran
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}"
                            href="{{ route('admin.activity-logs') }}"
                        >
                            Aktivitas
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/data/guru*') ? 'active' : '' }}"
                            href="{{ route('admin.gurus.index') }}"
                        >
                            Guru
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/data/siswa*') ? 'active' : '' }}"
                            href="{{ route('admin.siswas.index') }}"
                        >
                            Siswa
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/data/kelas*') ? 'active' : '' }}"
                            href="{{ route('admin.kelas.index') }}"
                        >
                            Kelas
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/data/mapel*') ? 'active' : '' }}"
                            href="{{ route('admin.mapel.index') }}"
                        >
                            Mapel
                        </a>

                        <a
                            class="mobile-nav-link {{ request()->is('admin/data/jam*') ? 'active' : '' }}"
                            href="{{ route('admin.jam.index') }}"
                        >
                            Jam
                        </a>

                    @endif

                </nav>

                <main class="content">

                    @if (session('success'))
                        <div class="alert success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @yield('content')

                </main>

            </div>

        </div>

    @else

        @yield('content')

    @endauth

    <script>
        document.querySelectorAll('[data-live-clock]').forEach((clock) => {
            const serverTime = Number(clock.dataset.serverTime);
            const timezone = clock.dataset.timezone;
            const startedAt = performance.now();
            const formatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            });

            const updateClock = () => {
                const currentTime = new Date(serverTime + (performance.now() - startedAt));
                clock.textContent = formatter.format(currentTime);
                clock.dateTime = currentTime.toISOString();
            };

            updateClock();
            window.setInterval(updateClock, 1000);
        });

        document
            .querySelectorAll('[data-menu-toggle], [data-menu-close]')
            .forEach((element) => {

                element.addEventListener('click', () => {

                    const shell = document.querySelector('.app-shell');

                    if (!shell) {
                        return;
                    }

                    const isCloseButton = element.matches('[data-menu-close]');
                    const isOpen = isCloseButton ? false : !shell.classList.contains('menu-open');

                    shell.classList.toggle('menu-open', isOpen);

                    document
                        .querySelector('[data-menu-toggle]')
                        ?.setAttribute(
                            'aria-expanded',
                            String(isOpen)
                        );
                });
            });
    </script>

</body>

</html>
