# Report Export Specifications

## Overview
The reporting system allows **staff users only** to export data from the inventory management system. Reports are available in **Excel (.xlsx)** and **CSV** formats.

---

## 1. Stock Report (Laporan Stok)

### Purpose
Export current stock levels and adjustment history for all materials.

### Data Included

#### Stock Overview Sheet
| Column | Description | Example |
|--------|-------------|---------|
| No | Row number | 1 |
| Kode Material | Material ID | MAT001 |
| Nama Material | Material name | Kabel Listrik 2.5mm |
| Kategori | Category name | Elektronik |
| Satuan | Unit of measurement | meter |
| Stok Saat Ini | Current stock quantity | 250 |
| Total Masuk | Total stock received | 1000 |
| Total Keluar | Total stock issued | 750 |
| Status | Stock status | Tersedia / Menipis / Habis |
| Terakhir Diperbarui | Last updated timestamp | 2025-10-19 10:30:00 |

#### Stock Adjustment History Sheet (Optional)
| Column | Description | Example |
|--------|-------------|---------|
| No | Row number | 1 |
| Tanggal | Adjustment date | 2025-10-19 |
| Material | Material name | Kabel Listrik 2.5mm |
| Kategori | Category name | Elektronik |
| Jenis | Adjustment type | Manual / Permintaan / Pengembalian |
| Stok Sebelum | Quantity before | 100 |
| Penyesuaian | Adjustment amount | +150 |
| Stok Sesudah | Quantity after | 250 |
| User | User who made adjustment | John Doe |
| Catatan | Notes | Stok masuk dari supplier |

### Filters Available
- Date Range (for adjustment history)
- Category
- Stock Status (All / Low Stock / Out of Stock)
- Material Name (search)

---

## 2. Request Materials Report (Laporan Permintaan Material)

### Purpose
Export all material requests with approval status and tracking information.

### Data Included

| Column | Description | Example |
|--------|-------------|---------|
| No | Row number | 1 |
| Nomor Permintaan | Request number | REQ-ABC12345 |
| Tanggal Permintaan | Request date | 2025-10-15 |
| Pemohon | Requester name | Andi Susanto |
| Role Pemohon | Requester role | produksi |
| Material | Material name | Kabel Listrik 2.5mm |
| Kategori | Category name | Elektronik |
| Jumlah | Quantity requested | 100 |
| Satuan | Unit | meter |
| Status | Request status | Pending / Disetujui / Ditolak |
| Disetujui Oleh | Approver name | Sarah Manager |
| Tanggal Disetujui | Approval date | 2025-10-16 |
| Catatan | Notes/remarks | Untuk proyek A |
| Durasi Proses | Processing time | 1 hari |

### Filters Available
- Date Range (request date)
- Status (All / Pending / Approved / Rejected)
- Category
- Requester (user)
- Material Name (search)

### Summary Statistics (Header Section)
- Total Requests
- Approved Requests
- Pending Requests
- Rejected Requests
- Total Quantity Approved
- Average Processing Time

---

## 3. Return Materials Report (Laporan Pengembalian Material)

### Purpose
Export all material returns with approval status and tracking information.

### Data Included

| Column | Description | Example |
|--------|-------------|---------|
| No | Row number | 1 |
| Nomor Pengembalian | Return number | RET-XYZ67890 |
| Nomor Permintaan Asal | Original request number | REQ-ABC12345 |
| Tanggal Pengembalian | Return date | 2025-10-18 |
| Pengembalian Oleh | Returner name | Andi Susanto |
| Role Pengembalian | Returner role | produksi |
| Material | Material name | Kabel Listrik 2.5mm |
| Kategori | Category name | Elektronik |
| Jumlah Diminta Awal | Original requested qty | 100 |
| Jumlah Dikembalikan | Quantity returned | 50 |
| Satuan | Unit | meter |
| Status | Return status | Pending / Disetujui / Ditolak |
| Disetujui Oleh | Approver name | Sarah Manager |
| Tanggal Disetujui | Approval date | 2025-10-19 |
| Catatan | Notes/remarks | Sisa material proyek A |
| Durasi Proses | Processing time | 1 hari |

### Filters Available
- Date Range (return date)
- Status (All / Pending / Approved / Rejected)
- Category
- Returner (user)
- Material Name (search)

### Summary Statistics (Header Section)
- Total Returns
- Approved Returns
- Pending Returns
- Rejected Returns
- Total Quantity Returned
- Average Processing Time

---

## Access Control

### Permission: `export-reports`
- **Required Role:** Staff only
- **Applies To:** All report export functionality

### UI Restrictions
- Report menu only visible to users with `staff` role
- Export buttons only shown to users with `export-reports` permission
- Direct URL access blocked with 403 Forbidden for non-staff users

---

## Technical Requirements

### Export Library
- **Recommended:** Laravel Excel (maatwebsite/excel)
- Supports both XLSX and CSV formats
- Memory efficient for large datasets
- Built-in styling and formatting

### File Naming Convention
```
{report_type}_{date_range}_{timestamp}.xlsx

Examples:
- stock_report_2025-10-19.xlsx
- requests_report_2025-10-01_to_2025-10-19_1697707200.xlsx
- returns_report_all_time_1697707200.xlsx
```

### Performance Considerations
- Use chunking for large datasets (>10,000 rows)
- Add loading indicators in UI
- Queue export jobs for very large reports
- Set max execution time for export operations

---

## Implementation Phases

### Phase 1: Basic Export
- [x] Create ReportController
- [ ] Install Laravel Excel package
- [ ] Implement basic stock export
- [ ] Add export buttons to views

### Phase 2: Advanced Features
- [ ] Add all filters
- [ ] Implement requests export
- [ ] Implement returns export
- [ ] Add summary statistics

### Phase 3: UI Enhancement
- [ ] Create dedicated report pages
- [ ] Add date range pickers
- [ ] Show preview before export
- [ ] Add export format selection (XLSX/CSV)

### Phase 4: Optimization
- [ ] Add export queuing
- [ ] Implement progress indicators
- [ ] Add export history log
- [ ] Email export link when ready

---

## Sample Export Preview

### Stock Report Example
```
PT MARUWA - Laporan Stok Material
Periode: 01 Oktober 2025 - 19 Oktober 2025
Dicetak: 19 Oktober 2025 14:30
Oleh: Admin User

No | Material | Kategori | Satuan | Stok | Status
1  | Kabel 2.5mm | Elektronik | meter | 250 | Tersedia
2  | Cat Tembok | Bahan Bangunan | kg | 5 | Menipis
3  | Pipa PVC | Plumbing | meter | 0 | Habis

Total Material: 3
Total Stok Tersedia: 255 unit
Material Menipis: 1
Material Habis: 1
```

---

## Future Enhancements
- [ ] PDF export with company letterhead
- [ ] Schedule automatic weekly/monthly reports
- [ ] Dashboard widget showing export history
- [ ] Custom report builder (select columns)
- [ ] Chart/graph exports
- [ ] Comparison reports (month-to-month)
