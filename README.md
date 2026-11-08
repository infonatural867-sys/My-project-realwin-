# REALWIN

A clean-room Android app and PHP/MySQL backend starter for a non-cash, non-redeemable entertainment-points platform.

## Safety and scope
- Points in this starter are entertainment-only and have no cash value.
- No deposits, withdrawals, cash-out, wagering, or real-money gaming are implemented.
- Do not add payment flows or redeemable balances without qualified legal review and compliant provider approval.

## Structure
- `android/` — native Android app (Java + Android Gradle Plugin)
- `backend/` — PHP API starter and MySQL schema for shared hosting
- `.github/workflows/` — automated debug APK build

## Build
Open the `android/` directory in Android Studio, or run the GitHub Actions workflow `Android APK` to build a debug APK.

## Backend setup
See `backend/README.md`. Configure database credentials on the server; never commit secrets.

## Status
Initial clean-room scaffold. It is not a production-ready service until backend deployment, security review, and device testing are completed.
