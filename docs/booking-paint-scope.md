# Paint Scope, Damage Photos, and Shop Pricing Specification

## 1. Overview
AutoProject+ incorporates a structured paint and damage inspection workflow during booking. Instead of arbitrary high-tier estimates, initial estimates adhere directly to the shop owner's verified price sheet. Customers and staff can select vehicle panels, designate per-panel damage severity, and upload 1–6 reference damage photos for comprehensive scope definition prior to initial approval and physical inspection.

---

## 2. Shop Pricing Matrix

The following baseline ranges are seeded via `ServiceCatalogSeeder` and enforced during initial quotation calculation:

| Service Code | Service Name | Estimate Range (PHP) | Panel Constraints | Damage Photos (1–6) | Brand Picker |
|---|---|---|---|---|---|
| `paint-001` | Paint touch up | ₱3,000 – ₱4,000 | 1 to 2 panels required | **Required** | Disabled |
| `paint-002` | Custom paint job | ₱15,000 – ₱20,000 | At least 1 panel required | **Required** | Disabled |
| `body-001` | Body repair | ₱5,000 – ₱7,000 | At least 1 panel required | **Required** | Disabled |
| `pkg-001` | Full custom build package | ₱35,000 – ₱45,000 | Optional panels | **Required** | Disabled |
| `perf-exhaust` | Custom exhaust piping | ₱7,000 – ₱10,000 | N/A | Optional | Enabled |
| `perf-intake` | Custom intake piping | ₱6,000 – ₱8,000 | N/A | Optional | Enabled |
| `perf-intercooler` | Intercooler piping & install | ₱12,000 – ₱15,000 | N/A | Optional | Enabled |
| `maint-general` | General maintenance & checkup | ₱2,000 – ₱3,000 | N/A | Optional | Enabled |

*Note: Legacy service `ext-001` (Exterior Detailing) is deactivated in favor of the specialized paint and body workflow.*

---

## 3. Business Rules

### A. Mutual Exclusion of Primary Paint Services
A booking may select at most **one** primary paint service:
- `paint-001` (Paint touch up)
- `paint-002` (Custom paint job)
- `pkg-001` (Full custom build package)

Attempting to book multiple primary paint services is prevented client-side and blocked by server-side validation (`StoreBookingRequest` and `WalkInBookingController`).

### B. Auto-Trigger for Body Repair (`body-001`)
When booking a paint or package service (`paint-001`, `paint-002`, or `pkg-001`), each selected panel includes a damage severity classification:
- `light_scratch`
- `paint_only`
- `dent`
- `body_work`
- `replace_panel`

If **any** panel is marked with `dent` or `body_work`:
1. If `body-001` is not already explicitly added in the selected services list, `QuotationBuilderService` automatically appends a dedicated quotation line item:
   - Line Item: `"Body repair (dent / body work required on: <panels>)"`
   - Additional Range: **₱5,000 – ₱7,000**
2. The quotation is added at most **once** per booking regardless of how many panels have dents or body work.

### C. Damage Photos
- Required (1 to 6 images, max 5MB each, JPEG/PNG) whenever any paint-related service (`paint-001`, `paint-002`, `body-001`, `pkg-001`) is chosen.
- Stored on the `public` disk in the `booking_damage_photos/` directory and tracked in the `booking_attachments` table (`type = 'damage_photo'`).

---

## 4. Scope Presets

The booking interfaces provide fast preset shortcuts:
- **Full Car**: Selects all 18 standard body panels (hood, bumpers, fenders, doors, quarter panels, roof, trunk, mirrors, spoilers, side skirts).
- **Front End**: Hood, Front Bumper, Left Front Fender, Right Front Fender.
- **Rear End**: Trunk / Tailgate, Rear Bumper, Left Rear Quarter Panel, Right Rear Quarter Panel.
- **Clear**: Resets panel selections.

---

## 5. Database Schema & Architecture

### Tables
1. **`paint_panels`**:
   - `code` (string, unique): Panel identifier (e.g. `hood`, `front_bumper`).
   - `name` (string): Display name.
   - `zone` (enum): `front`, `rear`, `side_left`, `side_right`, `top`, `other`.
   - `sort_order`, `is_active`.
2. **`booking_paint_scopes`**:
   - `booking_id` (foreign key to `bookings`).
   - `scope_mode`: `touch_up`, `custom_paint`, `body_repair`, `full_build`, or `custom`.
   - `color_code`, `finish_type`, `notes`.
3. **`booking_paint_panels`**:
   - `booking_id`, `paint_panel_id`.
   - `damage_severity`: `light_scratch`, `paint_only`, `dent`, `body_work`, `replace_panel`.
   - `notes`.
4. **`booking_attachments`**:
   - `booking_id`, `uploader_id`.
   - `type`: `damage_photo`, `payment_receipt`, `inspection_sheet`, `general`.
   - `file_path`, `original_name`, `mime_type`, `file_size`.

### Key Services & Controllers
- `App\Services\Booking\QuotationBuilderService`: Computes estimates with dynamic panel descriptions, zero-brand override for paint, and auto-body repair logic.
- `App\Services\Booking\BookingCreatorService`: Persists booking scopes, panels, and damage photo files.
- `App\Http\Controllers\Customer\BookingController`: Exposes `paintEstimate()` endpoint for live AJAX estimate calculation as panels and severity toggle.
- `App\Http\Controllers\Staff\WalkInBookingController`: Enables walk-in desk encoding with the identical panel, scope, and photo capabilities.
- `App\Http\Controllers\Admin\BookingApprovalController`: Eager-loads paint relationships and displays full panel checklists and photo galleries during approval.
