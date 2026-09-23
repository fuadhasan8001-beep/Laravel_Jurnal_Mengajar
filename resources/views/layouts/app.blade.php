<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Jurnal Guru') | Jurnal Guru</title>

    <style>
        :root {
            --ink: #202b27;
            --muted: #596961;
            --brand-blue: #2453d4;
            --brand-blue-dark: #163fa8;
            --action-color: #2453d4;
            --brand-blue-light: #3470de;
            --brand-yellow: #fff176;
            --brand-yellow-soft: #fffde7;
            --brand-green: #23852b;
            --success-color: #23852b;
            --brand-green-dark: #196622;
            --surface-soft: #f3f7f5;
            --surface: #ffffff;
            --action-background: #2453d4;
            --pale: #f3f7f5;
            --line: #dce5e0;
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
            color: var(--brand-green-dark);
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

            background: #196622;
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
            background: var(--brand-yellow);
            color: var(--brand-blue-dark);
            box-shadow: 0 3px 10px rgba(25, 65, 40, .16);
        }

        .nav-label {
            padding: 0 12px 10px;

            color: #d6ebd7;
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

            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #2453d4;
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

            border-top: 1px solid rgba(255, 255, 255, .4);
            padding: 18px 10px 0;
        }

        .user-mini {
            display: flex;
            align-items: center;
            gap: 10px;

            color: #ffffff;
        }

        .avatar {
            display: flex;
            width: 35px;
            height: 35px;

            align-items: center;
            justify-content: center;

            border-radius: 50%;
            background: var(--surface-soft);
            color: var(--brand-green-dark);

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
            color: #d6ebd7;
            font-size: 11px;
            text-transform: capitalize;
        }

        .logout {
            width: 100%;
            margin-top: 16px;

            min-height: 42px;
            padding: 10px;

            border: 1px solid rgba(255, 255, 255, .55);
            border-radius: 8px;

            background: transparent;
            color: #ffffff;

            cursor: pointer;

            font-size: 13px;
            font-weight: 700;
        }

        .logout:hover {
            border-color: var(--action-color);
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

            min-height: 74px;

            align-items: center;
            justify-content: space-between;
            gap: 24px;

            border-bottom: 1px solid #e2d66a;
            background: var(--brand-yellow);
            color: var(--ink);

            padding: 0 42px;
        }

        .topbar-brand {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .eyebrow {
            color: #52665b;
            font-size: 12px;
        }

        .topbar-title {
            margin: 3px 0 0;
            font-size: 18px;
        }

        .topbar-actions {
            display: flex;
            min-width: 0;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
        }

        .date-chip {
            display: flex;
            align-items: center;
            gap: 8px;

            min-width: 0;
            border: 1px solid rgba(255, 255, 255, .5);
            border-radius: 10px;
            padding: 7px 11px;

            background: rgba(255, 255, 255, .34);
            color: #52665b;
            font-size: 13px;
        }

        .date-chip > div {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 1px;
            line-height: 1.2;
        }

        .date-chip svg {
            flex: none;
        }

        .live-clock {
            color: var(--ink);
            font-size: 13px;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .menu-toggle {
            display: none;

            width: 38px;
            height: 38px;

            align-items: center;
            justify-content: center;

            border: 1px solid var(--line);
            border-radius: 8px;

            background: rgba(255, 255, 255, .82);
            color: var(--ink);

            cursor: pointer;
        }

        /* =========================================================
           NOTIFICATION
        ========================================================= */

        .notification-menu {
            position: relative;
            flex: none;
        }

        .notification-button {
            position: relative;
            display: inline-flex;
            min-height: 42px;
            min-width: 44px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid var(--line);
            border-radius: 8px;

            padding: 8px 10px;

            background: var(--surface);
            color: var(--ink);

            cursor: pointer;

            font-size: 12px;
            font-weight: 700;
        }

        .notification-button::-webkit-details-marker {
            display: none;
        }

        .notification-button svg {
            width: 18px;
            height: 18px;
            flex: none;
        }

        .notification-label {
            white-space: nowrap;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .notification-badge {
            display: inline-flex;
            min-width: 17px;
            height: 17px;
            align-items: center;
            justify-content: center;
            border-radius: 99px;
            padding: 0 4px;
            background: var(--brand-green-dark);
            color: #ffffff;
            font-size: 10px;
            line-height: 1;
        }

        .notification-list {
            position: absolute;
            z-index: 100;

            top: calc(100% + 8px);
            right: 0;

            width: min(320px, calc(100vw - 32px));

            border: 1px solid var(--line);
            border-radius: 10px;

            background: var(--surface);

            box-shadow: 0 12px 30px rgba(25, 65, 40, .18);

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
            background: var(--surface-soft);
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

            background: var(--action-background);
            color: var(--ink);
            color: #fff;

            cursor: pointer;

            font-size: 14px;
            font-weight: 700;

            box-shadow: 0 5px 12px rgba(25, 65, 40, .24);
        }

        .btn:hover {
            background: var(--brand-green-dark);
            color: #fff;
            text-decoration: none;
        }

        .btn-muted {
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--ink);

            box-shadow: none;
        }

        .btn-muted:hover {
            background: #edf2ef;
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

            background: var(--surface);

            box-shadow: 0 8px 24px rgba(89, 96, 76, .1);
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

            background: var(--surface-soft);
            color: var(--brand-green-dark);
        }

        .stat-icon.green {
            background: #e8f8f0;
            color: var(--green);
        }

        .stat-icon.amber {
            background: #fff9c4;
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
            background: var(--brand-yellow-soft);

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
            background: #edf2ef;
            color: var(--muted);

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
            background: var(--surface);
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

        .notification-menu details[open] .notification-button {
            border-color: var(--brand-blue-light);
            box-shadow: 0 0 0 3px rgba(36, 83, 212, .18);
        }

        .topbar-user {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 9px;
            border: 1px solid rgba(255, 255, 255, .55);
            border-radius: 10px;
            padding: 5px 10px 5px 6px;
            background: rgba(255, 255, 255, .34);
            color: var(--ink);
        }

        .topbar-user:hover {
            text-decoration: none;
            background: rgba(255, 255, 255, .58);
        }

        .topbar-user .avatar {
            width: 30px;
            height: 30px;
            background: var(--surface);
            color: var(--brand-blue-dark);
            font-size: 12px;
        }

        .topbar-user span:last-child {
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
        }

        .status.pending {
            background: #fff9c4;
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

            border: 1px solid #d6e1da;
            border-radius: 8px;

            padding: 11px 12px;

            outline: 0;

            background: var(--surface);
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
            border-color: var(--brand-green);

            box-shadow: 0 0 0 4px rgba(36, 83, 212, .24);
        }

        .field-readonly {
            border: 1px solid #d6e1da;
            border-radius: 8px;

            padding: 11px 12px;

            background: #edf2ef;
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

            border: 1px solid #dce5e0;
            border-radius: 14px;

            background: var(--surface);

            overflow: hidden;
        }

        .journal-card-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 16px;

            border-bottom: 1px solid #dce5e0;

            padding: 18px 20px;
        }

        .journal-card-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .journal-card-header p {
            margin: 4px 0 0;

            color: #596961;
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

            background: #edf2ef;
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
            color: #596961;
            font-size: 11px;
        }

        .journal-info strong {
            color: var(--ink);
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

            background: var(--surface);
        }

        .quick-card:hover {
            border-color: #2453d4;

            background: #f3f7f5;

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

            border: 1px dashed #dce5e0;
            border-radius: 10px;

            padding: 15px;

            color: #596961;

            text-align: center;

            font-size: 11px;
        }

        .signature-canvas {
            width: 100%;
            min-height: 180px;

            border: 1px dashed #839c8b;
            border-radius: 10px;

            background: var(--surface);

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

        .submit-summary {
            margin-top: 24px;
            padding: 18px;
            border: 1px solid var(--action-color);
            border-radius: 10px;
            background: var(--surface-soft);
        }

        .submit-summary h3 {
            margin: 0 0 12px;
        }

        .submit-summary dl {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 20px;
            margin: 0;
        }

        .submit-summary dl div {
            min-width: 0;
        }

        .submit-summary dt {
            color: var(--muted);
            font-size: 12px;
        }

        .submit-summary dd {
            margin: 2px 0 0;
            overflow-wrap: anywhere;
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

            .topbar {
                gap: 16px;
            }

            .topbar-actions {
                gap: 9px;
            }

            .topbar-user span:last-child,
            .notification-label {
                display: none;
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
            .submit-summary dl {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;

                z-index: 50;

                top: 0;
                left: 0;

                width: min(82vw, 300px);
                height: 100vh;

                padding: 22px 18px;

                overflow-y: auto;

                background: #196622;

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
                min-height: 66px;

                padding: 0 18px;
            }

            .topbar-brand {
                min-width: 0;
                gap: 11px;
            }

            .topbar-brand .eyebrow {
                display: none;
            }

            .topbar-title {
                margin: 0;
                font-size: 17px;
                line-height: 1.15;
            }

            .topbar-actions {
                display: none;
            }

            .mobile-top-actions {
                display: grid;
                width: 132px;
                min-width: 132px;
                grid-template-columns: auto auto;
                grid-template-rows: auto auto;
                align-items: center;
                justify-items: end;
                gap: 5px 8px;
            }

            .mobile-live-clock {
                grid-column: 1 / -1;
                justify-self: end;
                font-size: 12px;
                line-height: 1;
            }

            .mobile-top-actions .notification-menu,
            .mobile-top-actions > form {
                grid-row: 2;
            }

            .mobile-top-actions .notification-menu {
                grid-column: 1;
            }

            .mobile-top-actions > form {
                grid-column: 2;
            }

            .mobile-logout-button {
                flex: 0 0 auto;

                display: inline-flex;

                align-items: center;
                justify-content: center;

                border: 1px solid var(--line);
                border-radius: 8px;

                padding: 8px 10px;

                background: var(--surface);
                color: var(--ink);

                cursor: pointer;

                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
            }

            .mobile-top-actions .notification-list {
                position: fixed;

                top: 72px;
                right: 16px;
                left: 16px;

                width: auto;

                max-width: none;
            }

            .mobile-notification-button {
                width: 42px;
                padding: 8px;
            }

            .mobile-notification-button .notification-label {
                display: none;
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
                border-color: var(--brand-green);

                background: var(--brand-green);
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

                    <div class="topbar-brand">

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

                    <div class="topbar-actions">

                        <div class="date-chip">

                            <svg
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect x="3" y="4" width="18" height="17" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>

                            <div>
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

                        </div>

                        <div class="notification-menu">

                            <details>

                                <summary class="notification-button" aria-label="Notifikasi">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
                                    </svg>
                                    <span class="notification-label">Notifikasi</span>
                                    <span class="notification-badge">{{ auth()->user()->unreadNotifications()->count() }}</span>
                                    <span class="sr-only">Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})</span>
                                </summary>

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

                        <a class="topbar-user" href="{{ route('profile') }}" aria-label="Buka profil {{ auth()->user()->name }}">
                            <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>

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

                                <summary class="notification-button mobile-notification-button" aria-label="Notifikasi">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
                                    </svg>
                                    <span class="notification-label">Notifikasi</span>
                                    <span class="notification-badge">{{ auth()->user()->unreadNotifications()->count() }}</span>
                                    <span class="sr-only">Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})</span>
                                </summary>

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
    @stack('scripts')

</body>

</html>
