@pushOnce('styles', 'attendance-editor')
<style>
    .attendance-editor { width: 100%; overflow: visible; }
    .attendance-editor table { width: 100%; table-layout: fixed; min-width: 0; }
    .attendance-editor th, .attendance-editor td { white-space: normal; overflow-wrap: anywhere; vertical-align: top; padding: 14px 12px; }
    .attendance-editor th:first-child { width: 28%; }
    .attendance-editor th:nth-last-child(2) { width: 46%; }
    .attendance-editor th.attendance-number { width: 44px; }
    .attendance-editor table:has(th.attendance-number) th:nth-child(2) { width: 27%; }
    .attendance-editor .attendance-name small { display: block; margin-top: 4px; color: var(--muted, #666); }
    .attendance-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4px 10px; }
    .attendance-options label { display: flex; align-items: center; gap: 6px; min-height: 44px; margin: 0; font-size: 13px; cursor: pointer; }
    .attendance-options label:has(input:disabled) { cursor: default; color: var(--muted, #666); }
    .attendance-options input[type=radio] { flex: 0 0 18px; width: 18px; height: 18px; margin: 0; padding: 0; accent-color: var(--gold-dark, #846013); }
    .attendance-editor input[type=text], .attendance-editor input:not([type]) { width: 100%; min-width: 0; box-sizing: border-box; }
    .attendance-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; padding: 16px 0; }
    .attendance-toolbar input { flex: 1 1 220px; min-width: 0; }
    .attendance-toolbar button { min-width: 44px; min-height: 44px; }
    @media (max-width: 1100px) {
        .attendance-editor table, .attendance-editor tbody { display: block; }
        .attendance-editor thead { display: none; }
        .attendance-editor tbody > tr { display: grid; grid-template-columns: minmax(0, 1fr); border-bottom: 1px solid var(--border, #ddd); padding: 14px 0; }
        .attendance-editor td { display: block; padding: 4px 8px; border: 0; width: auto; }
        .attendance-editor td.attendance-number { float: left; width: auto; font-size: 12px; color: var(--muted, #666); }
        .attendance-editor .attendance-options { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .attendance-editor td:has(input[data-attendance-note][hidden]), .attendance-editor td:has(input[data-absence-note][hidden]):not(:has(a)) { display: none; }
    }
    @media (max-width: 520px) {
        .attendance-editor .attendance-options { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .attendance-options label { font-size: 12px; }
    }
</style>
@endPushOnce
