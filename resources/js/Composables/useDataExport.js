/**
 * Composable for exporting data to CSV/Excel formats
 * Handles Bangladesh-specific formatting
 */
export function useDataExport() {
    
    /**
     * Export data to CSV format
     * @param {Array} data - Array of objects to export
     * @param {Object} options - Export options
     */
    function exportToCSV(data, options = {}) {
        const {
            filename = 'export',
            columns = null, // Array of column configs: { key, label, formatter }
            includeHeaders = true,
            delimiter = ',',
            dateFormat = 'DD/MM/YYYY', // Bangladesh format
            currencySymbol = '৳',
            bom = true // Add BOM for Excel compatibility with UTF-8
        } = options;
        
        if (!data || data.length === 0) {
            console.warn('No data to export');
            return false;
        }
        
        // Determine columns from first row if not specified
        const cols = columns || Object.keys(data[0]).map(key => ({
            key,
            label: formatLabel(key),
            formatter: null
        }));
        
        const rows = [];
        
        // Add headers
        if (includeHeaders) {
            rows.push(cols.map(col => escapeCSV(col.label || col.key, delimiter)));
        }
        
        // Add data rows
        for (const item of data) {
            const row = cols.map(col => {
                let value = getNestedValue(item, col.key);
                
                // Apply custom formatter if provided
                if (col.formatter && typeof col.formatter === 'function') {
                    value = col.formatter(value, item);
                } else {
                    // Auto-format based on type
                    value = autoFormat(value, { dateFormat, currencySymbol });
                }
                
                return escapeCSV(value, delimiter);
            });
            rows.push(row);
        }
        
        // Build CSV content
        let csvContent = rows.map(row => row.join(delimiter)).join('\n');
        
        // Add BOM for Excel UTF-8 compatibility
        if (bom) {
            csvContent = '\uFEFF' + csvContent;
        }
        
        // Download file
        downloadFile(csvContent, `${filename}.csv`, 'text/csv;charset=utf-8');
        
        return true;
    }
    
    /**
     * Export data to Excel-compatible format (actually CSV with Excel-friendly encoding)
     * For true Excel format, use a library like xlsx
     */
    function exportToExcel(data, options = {}) {
        return exportToCSV(data, {
            ...options,
            filename: options.filename || 'export',
            delimiter: ',',
            bom: true
        });
    }
    
    /**
     * Export data to JSON format
     */
    function exportToJSON(data, options = {}) {
        const {
            filename = 'export',
            pretty = true,
            columns = null
        } = options;
        
        if (!data || data.length === 0) {
            console.warn('No data to export');
            return false;
        }
        
        let exportData = data;
        
        // Filter columns if specified
        if (columns) {
            exportData = data.map(item => {
                const filtered = {};
                columns.forEach(col => {
                    const key = typeof col === 'string' ? col : col.key;
                    filtered[key] = getNestedValue(item, key);
                });
                return filtered;
            });
        }
        
        const jsonContent = pretty 
            ? JSON.stringify(exportData, null, 2) 
            : JSON.stringify(exportData);
        
        downloadFile(jsonContent, `${filename}.json`, 'application/json');
        
        return true;
    }
    
    /**
     * Print data as a formatted table
     */
    function printData(data, options = {}) {
        const {
            title = 'Data Export',
            columns = null,
            styles = ''
        } = options;
        
        if (!data || data.length === 0) {
            console.warn('No data to print');
            return false;
        }
        
        const cols = columns || Object.keys(data[0]).map(key => ({
            key,
            label: formatLabel(key)
        }));
        
        const printWindow = window.open('', '_blank');
        
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { margin-bottom: 20px; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f5f5f5; font-weight: bold; }
                    tr:nth-child(even) { background-color: #fafafa; }
                    .footer { margin-top: 20px; font-size: 12px; color: #666; }
                    @media print {
                        button { display: none; }
                    }
                    ${styles}
                </style>
            </head>
            <body>
                <h1>${title}</h1>
                <table>
                    <thead>
                        <tr>
                            ${cols.map(col => `<th>${col.label || col.key}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(item => `
                            <tr>
                                ${cols.map(col => {
                                    let value = getNestedValue(item, col.key);
                                    if (col.formatter) {
                                        value = col.formatter(value, item);
                                    } else {
                                        value = autoFormat(value);
                                    }
                                    return `<td>${escapeHTML(value)}</td>`;
                                }).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="footer">
                    <p>Exported on: ${new Date().toLocaleDateString('en-GB')} at ${new Date().toLocaleTimeString('en-GB')}</p>
                    <p>Total records: ${data.length}</p>
                </div>
                <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; cursor: pointer;">
                    Print
                </button>
            </body>
            </html>
        `;
        
        printWindow.document.write(html);
        printWindow.document.close();
        
        return true;
    }
    
    // Helper functions
    function escapeCSV(value, delimiter = ',') {
        if (value === null || value === undefined) return '';
        
        const str = String(value);
        
        // Escape if contains delimiter, quote, or newline
        if (str.includes(delimiter) || str.includes('"') || str.includes('\n') || str.includes('\r')) {
            return `"${str.replace(/"/g, '""')}"`;
        }
        
        return str;
    }
    
    function escapeHTML(value) {
        if (value === null || value === undefined) return '';
        
        const str = String(value);
        const escapeMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return str.replace(/[&<>"']/g, char => escapeMap[char]);
    }
    
    function formatLabel(key) {
        // Convert snake_case or camelCase to Title Case
        return key
            .replace(/_/g, ' ')
            .replace(/([A-Z])/g, ' $1')
            .replace(/^./, str => str.toUpperCase())
            .trim();
    }
    
    function getNestedValue(obj, path) {
        if (!path) return obj;
        
        const keys = path.split('.');
        let value = obj;
        
        for (const key of keys) {
            if (value === null || value === undefined) return '';
            value = value[key];
        }
        
        return value;
    }
    
    function autoFormat(value, options = {}) {
        const { dateFormat = 'DD/MM/YYYY', currencySymbol = '৳' } = options;
        
        if (value === null || value === undefined) return '';
        
        // Boolean
        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }
        
        // Date
        if (value instanceof Date) {
            return value.toLocaleDateString('en-GB');
        }
        
        // ISO date string
        if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
            try {
                const date = new Date(value);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleDateString('en-GB');
                }
            } catch {
                // Not a valid date
            }
        }
        
        // Array
        if (Array.isArray(value)) {
            return value.join(', ');
        }
        
        // Object
        if (typeof value === 'object') {
            return JSON.stringify(value);
        }
        
        return String(value);
    }
    
    function downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
    }
    
    return {
        exportToCSV,
        exportToExcel,
        exportToJSON,
        printData
    };
}

/**
 * Pre-defined column formatters for common data types
 */
export const columnFormatters = {
    // Bangladesh currency
    currency: (value) => {
        if (value === null || value === undefined) return '';
        const num = parseFloat(value);
        if (isNaN(num)) return value;
        return `৳${num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    },
    
    // Bangladesh date format
    date: (value) => {
        if (!value) return '';
        try {
            const date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleDateString('en-GB');
        } catch {
            return value;
        }
    },
    
    // Date and time
    datetime: (value) => {
        if (!value) return '';
        try {
            const date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleString('en-GB');
        } catch {
            return value;
        }
    },
    
    // Phone number
    phone: (value) => {
        if (!value) return '';
        const str = String(value).replace(/\D/g, '');
        if (str.length === 11 && str.startsWith('01')) {
            return `${str.slice(0, 5)}-${str.slice(5)}`;
        }
        return value;
    },
    
    // Boolean
    boolean: (value) => value ? 'Yes' : 'No',
    
    // Status badge (just text for export)
    status: (value) => {
        if (!value) return '';
        return String(value).charAt(0).toUpperCase() + String(value).slice(1);
    },
    
    // Percentage
    percentage: (value) => {
        if (value === null || value === undefined) return '';
        const num = parseFloat(value);
        if (isNaN(num)) return value;
        return `${num.toFixed(1)}%`;
    },
    
    // Number with thousand separators
    number: (value) => {
        if (value === null || value === undefined) return '';
        const num = parseFloat(value);
        if (isNaN(num)) return value;
        return num.toLocaleString('en-IN');
    }
};

export default useDataExport;
