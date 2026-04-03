# Project Guidelines

## Code Style

- Follow Laravel conventions and existing patterns in `src/app` and `src/tests`.
- Keep business logic in services/models, not Blade templates.
- Use Japanese labels/messages for end-user and admin UI where existing screens do so.
- Prefer small, focused changes; avoid unrelated refactors.

## Architecture

- Reservation flow entry points are in `src/routes/web.php` and `src/app/Http/Controllers/ReservationController.php`.
- Availability calculation belongs in `src/app/Services/AvailabilityService.php`.
- Notification sending belongs in `src/app/Services/NotificationService.php` and `src/app/Mail`.
- Admin access is controlled by `is_admin` via `src/app/Models/User.php` (`canAccessPanel`).

## Build And Test

- Primary environment is Docker Compose from repository root:
  - `docker compose up -d`
  - `docker exec -it sorairo_app php artisan migrate`
  - `docker exec -it sorairo_app php artisan test`
  - `docker exec -it sorairo_app php artisan pint`
- Frontend assets are built from `src`:
  - `npm install`
  - `npm run dev`
  - `npm run build`
- Local integrated dev runner (from `src`): `composer dev`.

## Conventions

- Time calculations use `Asia/Tokyo` and Carbon; preserve existing date/time formats.
- Prevent double booking using the current transaction + lock pattern in reservation creation.
- Keep compatibility with both reservation paths currently used by availability logic:
  - new reservations table range checks
  - legacy slot-based reserved ranges
- Reuse existing validation/request classes when adding or changing reservation inputs.

## References

- Setup and product-level documentation: `src/README.md`
- Detailed project guide and historical context: `AGENT.md`
