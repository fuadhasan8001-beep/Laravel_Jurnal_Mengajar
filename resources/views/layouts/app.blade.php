<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Jurnal Guru') | Jurnal Guru</title>

    <style>
        :root {
            --gold-dark: #8a6a32;
            --gold: #a8894a;
            --gold-soft: #c2a96b;
            --orange-muted: #9a7440;
            --orange-dark: #76552f;
            --cream: #f8f4ea;
            --warm-white: #fffdf8;
            --text-dark: #3f3a32;
            --text-muted: #756d60;
            --ink: #3f3a32;
            --muted: #756d60;
            --brand-blue: #a8894a;
            --brand-blue-dark: #8a6a32;
            --action-color: #a8894a;
            --brand-blue-light: #c2a96b;
            --brand-yellow: #f8f4ea;
            --brand-yellow-soft: #f8f4ea;
            --brand-green: #23852b;
            --success-color: #23852b;
            --brand-green-dark: #196622;
            --surface-soft: #f8f4ea;
            --surface: #fffdf8;
            --action-background: #8a6a32;
            --pale: #f8f4ea;
            --line: #ded2b8;
            --green: #11865b;
            --amber: #b16b00;
            --red: #c43b45;

            font-family: Inter, "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            font-synthesis: none;
            text-rendering: optimizeLegibility;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--pale);
            color: var(--ink);
            font-size: 15px;
            font-weight: 500;
            line-height: 1.55;
        }

        h1,
        h2,
        h3,
        h4 {
            color: var(--text-dark);
            font-weight: 800;
            line-height: 1.25;
        }

        a {
            color: var(--gold-dark);
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

        .searchable-select-shell {
            position: relative;
            width: 100%;
        }

        .searchable-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            min-height: 46px;
            padding: 0.7rem 0.9rem;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--surface);
            color: var(--ink);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(11, 18, 32, 0.03);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            text-align: left;
        }

        .searchable-select-trigger:hover,
        .searchable-select-trigger:focus-visible {
            border-color: rgba(168, 137, 74, 0.65);
            box-shadow: 0 0 0 4px rgba(168, 137, 74, 0.12);
            outline: none;
        }

        .searchable-select-label {
            display: block;
            overflow: hidden;
            color: var(--ink);
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .searchable-select-chevron {
            margin-left: 0.8rem;
            color: var(--muted);
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .searchable-select-shell.is-open .searchable-select-chevron {
            transform: rotate(180deg);
        }

        .searchable-select-menu {
            position: absolute;
            z-index: 60;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            display: none;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 18px 34px rgba(11, 18, 32, 0.12);
            overflow: hidden;
        }

        .searchable-select-shell.is-open .searchable-select-menu {
            display: block;
        }

        .searchable-select-search {
            width: 100%;
            border: 0;
            border-bottom: 1px solid var(--line);
            background: var(--cream);
            color: var(--ink);
            padding: 0.8rem 0.9rem;
            outline: none;
        }

        .searchable-select-search::placeholder {
            color: var(--muted);
        }

        .searchable-select-options {
            max-height: 240px;
            overflow-y: auto;
            background: var(--surface);
        }

        .searchable-select-option {
            display: block;
            width: 100%;
            border: 0;
            border-bottom: 1px solid rgba(220, 229, 224, 0.7);
            background: transparent;
            color: var(--ink);
            padding: 0.8rem 0.9rem;
            text-align: left;
            cursor: pointer;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .searchable-select-option:last-child {
            border-bottom: 0;
        }

        .searchable-select-option:hover,
        .searchable-select-option:focus-visible {
            background: rgba(168, 137, 74, 0.08);
            outline: none;
        }

        .searchable-select-option.is-selected {
            background: rgba(168, 137, 74, 0.14);
            color: var(--brand-blue-dark);
            font-weight: 700;
        }

        .searchable-select-option.is-empty {
            color: var(--muted);
            cursor: default;
        }

        select.searchable-hidden {
            display: none !important;
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
            width: 270px;
            height: 100vh;

            flex-direction: column;
            padding: 28px 18px;

            background: var(--gold-dark);
            color: #fff;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.35) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.28);
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
            background: var(--warm-white, #fffdf8);
            color: var(--brand-blue-dark);
            box-shadow: 0 3px 10px rgba(25, 65, 40, .16);
        }

        .nav-label {
            display: block;
            padding: 0 12px 9px;

            color: #f0e6d2;
            font-size: 10px;
            font-weight: 800;

            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-extra {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, .2);
        }

        .nav-extra > summary {
            cursor: pointer;
            list-style: none;
            border-radius: 8px;
            padding-top: 5px;
            padding-bottom: 10px;
        }

        .nav-extra > summary:hover,
        .nav-extra > summary:focus-visible {
            color: #fff;
            outline: none;
        }

        .nav-extra > summary::-webkit-details-marker {
            display: none;
        }

        .nav-extra > summary::after {
            content: '+';
            float: right;
            font-size: 15px;
            line-height: 1;
        }

        .nav-extra[open] > summary::after {
            content: '−';
        }

        .nav-label-account {
            margin-top: 18px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;

            min-height: 42px;
            padding: 10px 12px;

            border-radius: 9px;

            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, .14);
            color: #fff;
            text-decoration: none;
        }

        .nav-link.active {
            background: var(--warm-white);
            color: var(--text-dark);
        }

        .nav-link[aria-disabled="true"] {
            color: #ded2b8;
            cursor: not-allowed;
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

        .sidebar-datetime {
            display: grid;
            gap: 3px;
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
        }

        .sidebar-date {
            color: #f0e6d2;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .sidebar-clock {
            color: #fff;
            font-size: 20px;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: .04em;
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
            color: var(--gold-dark);

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

            border: 1px solid #efb6a8;
            border-radius: 10px;

            background: #a7463e;
            color: #ffffff;

            cursor: pointer;

            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 5px 14px rgba(35, 12, 10, .2);
        }

        .logout:hover {
            border-color: #ffd2c7;
            background: #89372f;
            color: #fff;
            text-decoration: none;
        }

        /* =========================================================
           MAIN
        ========================================================= */

        .main {
            width: calc(100% - 270px);
            margin-left: 270px;
            min-width: 0;
        }

        .topbar {
            display: flex;

            min-height: 74px;

            align-items: center;
            justify-content: space-between;
            gap: 24px;

            border-bottom: 1px solid var(--gold-soft);
            background: var(--warm-white);
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
            color: var(--text-muted, #6b6455);
            font-size: 12px;
        }

        .topbar-title {
            margin: 3px 0 0;
            font-size: 18px;
            font-weight: 800;
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
            color: var(--text-muted, #6b6455);
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
            color: var(--text-dark, #3d392f);
            font-size: 14px;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .menu-toggle {
            display: none;

            width: 42px;
            height: 42px;

            align-items: center;
            justify-content: center;

            border: 1px solid var(--line);
            border-radius: 12px;

            background: rgba(255, 255, 255, .82);
            color: var(--ink);

            cursor: pointer;
            font-size: 19px;
            font-weight: 800;
        }

        .menu-toggle:hover,
        .menu-toggle:focus-visible {
            border-color: var(--gold-dark);
            background: var(--cream);
            outline: 3px solid rgba(138, 106, 50, .2);
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

            background: var(--warm-white, #fffdf8);
            color: var(--ink);
            cursor: pointer;

            font-size: 13px;
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
            background: var(--gold-dark, #a98224);
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

            font-size: 14px;
            font-weight: 600;
            line-height: 1.45;
        }

        .notification-item:hover {
            background: var(--surface-soft);
        }

        .notification-empty {
            padding: 12px;
            color: var(--muted);
            font-size: 12px;
        }

        .mobile-top-actions {
            display: none;
        }

        .mobile-datetime {
            display: none;
        }

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
            max-width: 1600px;

            margin: 0 auto;
            padding: 34px clamp(22px, 3.2vw, 48px) 56px;
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
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .page-head p {
            margin: 7px 0 0;

            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
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
            font-weight: 800;

            box-shadow: 0 5px 12px rgba(25, 65, 40, .24);
        }

        .btn:hover {
            background: var(--orange-dark);
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
            background: var(--cream);
            color: var(--ink);
        }

        [role="navigation"][aria-label*="Pagination"] {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 12px;
        }

        [role="navigation"][aria-label*="Pagination"] a,
        [role="navigation"][aria-label*="Pagination"] span {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 7px 11px;
            background: var(--warm-white);
            color: var(--text-dark);
            font-size: 12px;
            line-height: 1;
        }

        [role="navigation"][aria-label*="Pagination"] a:hover {
            border-color: var(--gold);
            background: var(--cream);
            text-decoration: none;
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
            color: var(--gold-dark);
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
            font-size: 13px;
            font-weight: 700;
        }

        .stat-card strong {
            display: block;

            margin-top: 4px;

            font-size: 23px;
            font-weight: 800;
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
            font-weight: 800;
        }

        .panel-body {
            padding: 22px;
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

            font-size: 14px;
        }

        th {
            background: var(--cream);
            color: var(--muted);

            font-size: 12px;
            font-weight: 800;
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
            box-shadow: 0 0 0 3px rgba(168, 137, 74, .2);
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

        .detail-grid {
            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 18px 20px;

            margin: 0;
        }

        .detail-item {
            display: flex;

            min-width: 0;

            flex-direction: column;

            gap: 6px;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        .detail-grid dt {
            color: var(--muted);

            font-size: 12px;
            font-weight: 700;
        }

        .detail-grid dd {
            margin: 0;

            color: var(--ink);

            font-size: 15px;
            line-height: 1.5;

            overflow-wrap: anywhere;
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
            color: var(--text-dark);

            font-size: 14px;
            font-weight: 800;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            max-width: 100%;

            border: 1px solid #cfc4ad;
            border-radius: 10px;

            padding: 11px 12px;

            outline: 0;

            background: var(--surface);
            color: var(--ink);

            font-size: 15px;
            font-weight: 500;
        }

        .field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--gold);

            box-shadow: 0 0 0 4px rgba(196, 154, 58, .24);
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
            display: flex;
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

            background: var(--cream);
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
            border-color: var(--gold);

            background: var(--cream);

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
                width: min(82vw, 320px);
                height: 100vh;
                padding: 22px 18px;
                transform: translateX(-105%);
                visibility: hidden;
                pointer-events: none;
                transition: transform .2s ease, visibility .2s ease;
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
                background: rgba(25, 22, 17, .58);
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
            .sidebar-datetime {
                display: none;
            }

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

                background: var(--gold-dark);

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
                min-height: 0;
                flex-wrap: wrap;
                padding: 10px 12px;
                gap: 8px;
                background: #fffdf8;
            }

            .topbar-brand {
                flex: 1 1 100%;
                min-width: 0;
                gap: 8px;
            }

            .topbar-heading {
                display: flex;
                width: 100%;
                min-width: 0;
                align-items: center;
                justify-content: space-between;
                gap: 3px;
            }

            .topbar-brand .eyebrow {
                display: none;
            }

            .topbar-title {
                margin: 0;
                max-width: none;
                font-size: 17px;
                line-height: 1.2;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .topbar-actions {
                display: none;
            }

            .mobile-top-actions {
                display: grid;
                width: 100%;
                min-width: 100%;
                grid-template-columns: auto auto;
                align-items: center;
                justify-content: end;
                gap: 6px 8px;
            }

            .topbar-heading .mobile-datetime {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 3px;
            }

            .mobile-date {
                color: var(--text-dark);
                font-size: 10px;
                font-weight: 600;
                text-transform: capitalize;
            }

            .mobile-live-clock {
                flex: 0 0 auto;
                font-size: 17px;
                font-weight: 800;
                line-height: 1;
            }

            .mobile-top-actions > form {
                display: flex;
            }

            .mobile-logout-button {
                display: inline-flex;
                min-height: 40px;
                align-items: center;
                justify-content: center;
                border: 1px solid #efb6a8;
                border-radius: 10px;
                padding: 7px 10px;
                background: #a7463e;
                color: #fff;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap;
            }

            .mobile-logout-button:hover,
            .mobile-logout-button:focus-visible {
                background: #89372f;
                outline: 3px solid rgba(167, 70, 62, .2);
            }

            .mobile-top-actions .notification-list {
                position: fixed;

                top: 132px;
                right: 16px;
                left: 16px;

                width: auto;

                max-width: none;
            }

            .mobile-notification-button {
                width: auto;
                min-height: 40px;
                gap: 7px;
                padding: 7px 10px;
            }

            .mobile-notification-button .notification-label {
                display: inline;
            }

            .page-head {
                align-items: flex-start;

                flex-direction: column;

                margin-bottom: 22px;
            }

            .content {
                padding: 22px 16px 36px;
            }

            .panel-head {
                padding: 15px 16px;
            }

            .panel-body {
                padding: 16px;
            }

            th,
            td {
                padding: 11px 12px;
            }

            .page-head h1 {
                font-size: 24px;
                font-weight: 800;
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

            .stat-card small {
                font-size: 12px;
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

            .field input,
            .field select,
            .field textarea {
                min-height: 48px;
                font-size: 16px;
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

    @stack('styles')
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

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))
                        <a
                            class="nav-link {{ request()->is('absensi*') ? 'active' : '' }}"
                            href="{{ route('absensi.index') }}"
                        >
                            Kelola absensi
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['guru', 'piket'], true))
                        @if (auth()->user()->isPiketHariIni())
                            <a class="nav-link {{ request()->is('piket') || request()->is('dispensasi*') ? 'active' : '' }}" href="{{ url('/piket') }}">Menu piket</a>
                        @else
                            <span class="nav-link" aria-disabled="true" title="Menu aktif sesuai jadwal piket">Menu piket · tidak bertugas</span>
                        @endif
                    @endif

                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))
                        <a
                            class="nav-link {{ request()->is('jurnal*') ? 'active' : '' }}"
                            href="{{ route('jurnal.index') }}"
                        >
                            Jurnal mengajar
                        </a>
                    @endif

                    </nav>

                    <details class="nav-extra" @if (request()->is('rekap*') || request()->is('admin*') || request()->is('dispensasi*')) open @endif>
                        <summary class="nav-label">Menu tambahan</summary>
                        <nav class="nav-list" aria-label="Menu tambahan">
                    @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris', 'piket'], true))
                        <a
                            class="nav-link {{ request()->is('rekap*') ? 'active' : '' }}"
                            href="{{ route('laporan.jurnal') }}"
                        >
                            Rekap laporan
                        </a>
                    @endif

                    @if (auth()->user()->role === 'admin')
                        <a class="nav-link {{ request()->is('admin/jadwal-piket*') ? 'active' : '' }}" href="{{ route('admin.piket.index') }}">Jadwal piket guru</a>

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
                            class="nav-link {{ request()->is('admin/secretaries*') ? 'active' : '' }}"
                            href="{{ route('admin.secretaries.index') }}"
                        >
                            Pengurus kelas
                        </a>

                        <a
                            class="nav-link {{ request()->is('admin/data/guru*') ? 'active' : '' }}"
                            href="{{ route('admin.gurus.index') }}"
                        >
                            Guru
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

                    @if (in_array(auth()->user()->role, ['siswa', 'piket', 'admin'], true) || (auth()->user()->role === 'guru' && auth()->user()->isPiketHariIni()))
                        <a
                            class="nav-link {{ request()->is('dispensasi*') ? 'active' : '' }}"
                            href="{{ route('dispensasi.index') }}"
                        >
                            Dispensasi
                        </a>
                    @endif

                        </nav>
                    </details>

                    <div class="sidebar-datetime" aria-label="Tanggal dan waktu saat ini">
                        <span class="sidebar-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                        <time
                            class="sidebar-clock"
                            data-live-clock
                            data-server-time="{{ now()->getTimestampMs() }}"
                            data-timezone="{{ config('app.timezone') }}"
                            datetime="{{ now()->toIso8601String() }}"
                        >{{ now()->format('H:i:s') }}</time>
                    </div>

                    <div class="nav-label nav-label-account">Akun</div>
                    <nav class="nav-list" aria-label="Menu akun">
                    <a
                        class="nav-link {{ request()->is('profile') ? 'active' : '' }}"
                        href="{{ route('profile') }}"
                    >
                        Profil
                    </a>

                    @if (auth()->user()->role !== 'admin' && filled(config('app.admin_whatsapp')))
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

                        <div class="topbar-heading">
                            <div class="topbar-title">
                                Jurnal Guru
                            </div>
                            <div class="mobile-datetime">
                                <time
                                    class="live-clock mobile-live-clock"
                                    data-live-clock
                                    data-server-time="{{ now()->getTimestampMs() }}"
                                    data-timezone="{{ config('app.timezone') }}"
                                    datetime="{{ now()->toIso8601String() }}"
                                >
                                    {{ now()->format('H:i:s') }}
                                </time>
                                <span class="mobile-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="topbar-actions">
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

                                <div class="notification-list" data-notification-list data-notification-mode="desktop">

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

                                <div class="notification-list" data-notification-list data-notification-mode="mobile">

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

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="mobile-logout-button" type="submit">Keluar</button>
                        </form>

                    </div>

                </header>


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
        const closeNotificationMenus = () => {
            document.querySelectorAll('.notification-menu details').forEach((details) => {
                details.removeAttribute('open');
            });
        };

        const closeSidebarMenu = () => {
            const shell = document.querySelector('.app-shell');
            if (!shell) {
                return;
            }

            shell.classList.remove('menu-open');
            document.querySelector('[data-menu-toggle]')?.setAttribute('aria-expanded', 'false');
        };

        const applySearchableSelects = () => {
            document.querySelectorAll('select').forEach((select) => {
                if (select.dataset.searchableApplied === 'true' || select.multiple || select.classList.contains('searchable-hidden')) {
                    return;
                }

                const shouldSearch = select.options.length > 7 || select.dataset.searchable === 'true';
                if (!shouldSearch) {
                    return;
                }

                const shell = document.createElement('div');
                shell.className = 'searchable-select-shell';

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'searchable-select-trigger';
                trigger.setAttribute('aria-haspopup', 'listbox');
                trigger.setAttribute('aria-expanded', 'false');

                const label = document.createElement('span');
                label.className = 'searchable-select-label';

                const chevron = document.createElement('span');
                chevron.className = 'searchable-select-chevron';
                chevron.textContent = '▾';

                const menu = document.createElement('div');
                menu.className = 'searchable-select-menu';

                const search = document.createElement('input');
                search.type = 'search';
                search.className = 'searchable-select-search';
                search.placeholder = 'Cari opsi...';

                const list = document.createElement('div');
                list.className = 'searchable-select-options';

                const updateLabel = () => {
                    const selectedOption = Array.from(select.options).find((option) => option.selected);
                    const text = selectedOption ? selectedOption.textContent.trim() : 'Pilih opsi';
                    label.textContent = text;
                    Array.from(list.children).forEach((optionButton) => {
                        optionButton.classList.toggle('is-selected', optionButton.dataset.value === (selectedOption?.value ?? ''));
                    });
                };

                Array.from(select.options).forEach((option) => {
                    const optionButton = document.createElement('button');
                    optionButton.type = 'button';
                    optionButton.className = 'searchable-select-option';
                    optionButton.dataset.value = option.value;
                    optionButton.textContent = option.textContent.trim();

                    if (option.selected) {
                        optionButton.classList.add('is-selected');
                    }

                    optionButton.addEventListener('click', () => {
                        select.value = option.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        updateLabel();
                        shell.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');
                    });

                    list.appendChild(optionButton);
                });

                search.addEventListener('input', (event) => {
                    const query = event.target.value.trim().toLowerCase();
                    let hasVisibleOption = false;

                    Array.from(list.children).forEach((optionButton) => {
                        const matches = !query || optionButton.textContent.toLowerCase().includes(query);
                        optionButton.style.display = matches ? 'block' : 'none';
                        if (matches) {
                            hasVisibleOption = true;
                        }
                    });

                    if (!hasVisibleOption) {
                        const emptyState = list.querySelector('.searchable-select-option.is-empty');
                        if (!emptyState) {
                            const placeholder = document.createElement('div');
                            placeholder.className = 'searchable-select-option is-empty';
                            placeholder.textContent = 'Tidak ada opsi yang cocok';
                            list.appendChild(placeholder);
                        }
                    } else {
                        const emptyState = list.querySelector('.searchable-select-option.is-empty');
                        if (emptyState) {
                            emptyState.remove();
                        }
                    }
                });

                trigger.addEventListener('click', () => {
                    const isOpen = shell.classList.contains('is-open');
                    shell.classList.toggle('is-open', !isOpen);
                    trigger.setAttribute('aria-expanded', String(!isOpen));
                    if (!isOpen) {
                        search.focus();
                    }
                });

                trigger.append(label, chevron);
                menu.append(search, list);
                shell.append(trigger, menu);
                select.parentNode.insertBefore(shell, select);
                select.classList.add('searchable-hidden');
                select.dataset.searchableApplied = 'true';
                updateLabel();

                document.addEventListener('click', (event) => {
                    if (!shell.contains(event.target)) {
                        shell.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        };

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

                    closeNotificationMenus();

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

        document.querySelectorAll('.notification-menu details').forEach((details) => {
            details.addEventListener('toggle', () => {
                if (details.open) {
                    closeSidebarMenu();
                }
            });
        });

        const refreshNotifications = async () => {
            try {
                const response = await fetch('{{ route('notifications.feed') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;

                const feed = await response.json();
                document.querySelectorAll('.notification-badge').forEach((badge) => {
                    badge.textContent = feed.unread;
                });
                document.querySelectorAll('.notification-list[data-notification-list]').forEach((list) => {
                    const mode = list.dataset.notificationMode;
                    const limit = mode === 'mobile' ? 20 : 5;
                    list.querySelectorAll('form[data-live-notification], .notification-empty[data-live-notification]').forEach((item) => item.remove());
                    const items = feed.items.slice(0, limit);
                    if (!items.length) {
                        const empty = document.createElement('div');
                        empty.className = 'notification-empty';
                        empty.dataset.liveNotification = 'true';
                        empty.textContent = mode === 'mobile' ? 'Belum ada notifikasi.' : 'Tidak ada notifikasi baru.';
                        list.prepend(empty);
                        return;
                    }
                    items.slice().reverse().forEach((item) => {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = item.url;
                        form.dataset.liveNotification = 'true';
                        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="notification-item" type="submit">${item.message}${mode === 'mobile' ? `<br><small>${item.read ? 'Sudah dibaca' : 'Belum dibaca'} · ${item.created_at}</small>` : ''}</button>`;
                        list.prepend(form);
                    });
                });
            } catch (error) {
                // Notifications remain usable through the normal page-rendered list.
            }
        };

        window.setInterval(refreshNotifications, 10000);

        applySearchableSelects();
    </script>
    @stack('scripts')

</body>

</html>
