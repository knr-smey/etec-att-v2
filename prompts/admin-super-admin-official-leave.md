# Official Leave Feature — Admin & Super Admin Prompt

> Stack: Laravel + Vue 3 + Inertia
> Roles: `admin` (school office) and `super_admin`

Build the ADMIN & SUPER_ADMIN side of the "Official Leave" feature using
Laravel + Vue 3 + Inertia. Two roles: admin (school office) and
super_admin (higher privileges).

## Roles & Access

- `users.role ENUM('admin','super_admin')` or spatie/laravel-permission.
- Middleware `role:admin,super_admin` on all routes below.
- Policy: admins act only within their allowed actions; super_admin can
  override everything (revoke, delete, edit any record).
- Every mutating action writes an audit log row:
  `{user_id, action, leave_id, before/after JSON, ip, created_at}`.

## Admin Dashboard (office desk)

1. **Student search bar** (debounced): full_name or student ID → results
   card shows name, photo, class, course, current block status.
2. Click student → **"Request Leave"** button → modal generates QR code:
   - Signed URL route `leave.form`, 15-min expiry, SINGLE-USE.
   - Store session in `leave_request_sessions {student_id, token_hash,
     expires_at, used_at}`.
   - Modal shows countdown timer; regenerate button when expired.
   - After QR is scanned + submitted, screen LIVE-updates (polling every
     3s is fine) from "waiting..." to the review card.
3. **Review card**: student name/class, date range, reason,
   `[Reject] [Approve]` buttons.
   - Approve → `status='approved'`, `approved_by=auth id`,
     `approved_at=now()`.
   - Reject → requires short admin note, `status='rejected'`.
   - Confirm dialog before both actions.

## Leave History Page (admin)

- Table: student, class/course, dates, reason, status badge
  (pending yellow / approved green / rejected red / revoked gray),
  requested at, approved by.
- Filters: status, date range, class, search by student.
- Actions per row:
  - `pending` → Approve / Reject (same rules as review card)
  - `approved` → **REVOKE** (super_admin only OR admin if leave hasn't
    started yet): confirm dialog, `status='revoked'`, attendance becomes
    editable again for those dates.
  - `rejected/pending` → Delete (**super_admin only**), soft deletes.
- Pagination + export CSV button.

## Super Admin Extra Pages

1. **Reports & Stats**
   - Leaves per month chart, per class/course breakdown.
   - Top students by permission usage (quota watchlist: show X/4 used).
   - Students currently on approved leave today.
2. **Settings page** (config table or config file):
   - `monthly_permission_quota` (default 4)
   - `permissions_per_absence` (default 2)
   - `absence_block_threshold` (default 3)
   - `qr_token_ttl_minutes` (default 15)
   - Changes apply system-wide, logged in audit log.
3. **Admin activity log viewer**: filterable list of all audit rows —
   who approved/rejected/revoked what and when.

## Shared Rules

- Only `status='approved'` leaves lock attendance ("On Leave" badge);
  pending does NOT excuse anything.
- Validation on approve: no overlapping approved leaves for same
  student; reject with clear error message if conflict appeared meanwhile.
- All responses handle gracefully in UI: toasts on success/error,
  loading states, empty states, confirm dialogs for destructive actions.
- Vue pages: Dashboard (search+QR+review), LeaveHistory, Reports,
  Settings, ActivityLog — Inertia partial reloads after each action so
  badges/counters stay accurate without full refresh.

## Backend

- Migrations: `official_leaves`, `leave_request_sessions`, `audit_logs`,
  settings key/value table.
- Controllers: `LeaveRequestController` (QR generate, poll status),
  `LeaveApprovalController` (approve/reject/revoke), `ReportController`,
  `SettingController`, `ActivityLogController`.
- FormRequests for validation, Policies for role checks,
  single-use token check inside a DB transaction (lock row, verify
  unused + unexpired, mark used).

---

Super_admin = everything admin has **plus** revoke rights anytime,
reports, settings, activity log, hard delete.

## Reference Business Rules (from school policy)

| Rule | Value |
|---|---|
| Instructor permission quota | 4 / student / month |
| Conversion | 2 permissions = 1 equivalent absence |
| Block trigger | real_absences + converted ≥ 3 |
| Official leave quota | Unlimited (office approval required) |
| Max leave range per request | 30 days |

Official leave overrides everything at attendance time: instructor sees
"On Leave 🔒" badge and cannot mark the student absent; it never consumes
permission quota and never counts toward blocks/hard-lock.
