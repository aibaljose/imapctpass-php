# Payment ID Fix Documentation

## Problem Description
The ImpactPass system was experiencing issues with payment ID storage and retrieval. The primary issue was inconsistency between code using `razorpay_payment_id` column name while the database was using `payment_id` column name. This was causing "Unknown column" errors when processing payments.

## Solutions Implemented

### 1. Schema Check and Auto-upgrade
- Enhanced `checkAndUpgradeSchema()` in `api/apimethods.php` to check for both `payment_id` and `razorpay_payment_id` columns
- Added automatic column addition if either column is missing

### 2. Payment Verification Logic
- Updated `verifyPayment()` method to detect which column exists in the database
- Added smart handling to use the appropriate column name when updating the database
- Implemented a proper fallback strategy if neither column exists

### 3. Payment Form Handling
- Updated `book_event.php` to include both field names in the payment form
- Modified the Razorpay handler to set both payment ID fields for backward compatibility
- Ensured the payment form properly passes payment information to the verification endpoint

### 4. Verification Endpoint
- Updated `verify_payment.php` to accept either `payment_id` or `razorpay_payment_id`
- Added better error handling for missing payment ID values
- Enhanced logging of payment data for troubleshooting

### 5. Utility Scripts
- Enhanced `fix_payment_ids.php` to synchronize data between both column names
- Updated `check_columns.php` to provide detailed information about payment ID columns
- Added admin utility links to the events page for easy access to database tools

## Database Structure
The bookings table now supports both column names:
- `payment_id` - For backward compatibility
- `razorpay_payment_id` - For newer Razorpay integration

## How to Test
1. Create a new booking by selecting an event
2. Process the payment through Razorpay
3. Verify that the payment is correctly recorded in the database
4. Check that the booking status is properly updated to "completed"

## Future Recommendations
1. Consider standardizing on one column name (preferably `payment_id`) for consistency
2. Add more comprehensive error handling for payment failures
3. Implement regular database integrity checks to prevent schema issues

## Files Modified
- `api/apimethods.php`
- `verify_payment.php`
- `book_event.php`
- `check_columns.php`
- `events.php`

## Files Added/Enhanced
- `fix_payment_ids.php` (Enhanced)
- `payment_fix_documentation.md` (This file)