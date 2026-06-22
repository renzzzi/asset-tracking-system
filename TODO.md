# TODO - Checkout/Return (Transaction Log Feature)

## Step 1: Implement checkout endpoint
- Create `transactions/checkout.php` ✅
- Validate asset exists ✅
- Prevent duplicate active checkout (status=checked_out) ✅
- Insert into `transaction_logs` ✅
- Write audit log entry ✅

## Step 2: Implement return endpoint
- Create `transactions/return.php` ✅
- Mark active transaction returned ✅
- Write audit log entry ✅

## Step 3: Update asset details UI
- Update `assets/view.php` to display:
  - active checkout info (if any) ✅
  - Borrow/Checkout form (if none active) ✅
  - Return button (if active) ✅

## Step 4: Add any missing TransactionLog helpers
- (Checked) Current UI uses existing `TransactionLog::getActiveByAsset()` and `markReturned()` ✅


## Step 5: Quick manual test checklist
- Checkout an asset -> verify row inserted + UI updates
- Return -> verify returned_at + UI updates


