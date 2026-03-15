/**
 * Export data to CSV and trigger browser download.
 * @param {string[]} headers - Column header labels
 * @param {Array[]} rows     - Array of arrays (one per row)
 * @param {string}  filename - Download filename (without .csv)
 */
export function exportToCSV(headers, rows, filename = 'export') {
    const escape = (val) => {
        const s = String(val ?? '');
        return s.includes(',') || s.includes('"') || s.includes('\n')
            ? `"${s.replace(/"/g, '""')}"`
            : s;
    };

    const lines = [
        headers.map(escape).join(','),
        ...rows.map(row => row.map(escape).join(',')),
    ];

    const blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `${filename}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
