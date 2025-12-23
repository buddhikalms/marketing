<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $transaction_id = $_POST['transaction_id'];

    if ($user_id && $amount) {
        $conn->begin_transaction();
        try {
            // 1. Insert Payment Record
            $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, transaction_id, status) VALUES (?, ?, ?, 'completed')");
            $stmt->bind_param("ids", $user_id, $amount, $transaction_id);
            $stmt->execute();
            $payment_id = $stmt->insert_id;
            $stmt->close();

            // 2. Insert Sale Record (Required for Commissions table FK)
            $stmt_sale = $conn->prepare("INSERT INTO sales (user_id, amount) VALUES (?, ?)");
            $stmt_sale->bind_param("id", $user_id, $amount);
            $stmt_sale->execute();
            $sale_id = $stmt_sale->insert_id;
            $stmt_sale->close();

            // 3. Handle Recursive Commission Logic
            // Logic: Direct Referrer gets 2000 Rs + 1 Point.
            //        Ancestors (Referrer of Referrer, etc.) get 1 Point each.

            $current_user_id = $user_id;
            $is_direct_referrer = true;

            // Loop up the tree
            while (true) {
                // Get the referrer of the current user in the loop
                $q = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
                $q->bind_param("i", $current_user_id);
                $q->execute();
                $res = $q->get_result();

                if ($res->num_rows == 0) break; // User not found

                $row = $res->fetch_assoc();
                $parent_id = $row['parent_id'];
                $q->close();

                if (!$parent_id) break; // No more parents (Root node reached)

                // Calculate Rewards
                $points_to_give = 1;
                $cash_to_give = 0;

                if ($is_direct_referrer) {
                    $cash_to_give = 2000;
                }

                // Update Referrer's Wallet and Points
                $update = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ?, points = points + ? WHERE id = ?");
                $update->bind_param("dii", $cash_to_give, $points_to_give, $parent_id);
                $update->execute();
                $update->close();

                // Log Commission (Only if cash is involved, as per commissions table schema)
                if ($cash_to_give > 0) {
                    $log = $conn->prepare("INSERT INTO commissions (user_id, sale_id, amount) VALUES (?, ?, ?)");
                    $log->bind_param("iid", $parent_id, $sale_id, $cash_to_give);
                    $log->execute();
                    $log->close();
                }

                // Move up the tree
                $current_user_id = $parent_id;
                $is_direct_referrer = false; // Next iterations are ancestors, not direct
            }

            $conn->commit();

            // Redirect to the final printable invoice
            header("Location: invoice.php?id=" . $payment_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            die("Error processing payment: " . $e->getMessage());
        }
    }
}
header("Location: add_payment.php");
