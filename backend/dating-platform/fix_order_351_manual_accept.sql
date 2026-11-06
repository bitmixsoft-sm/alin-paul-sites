-- Manual fix for CCBill payment EUR 199 (2026-11-05 17:31, payer: Daniele Nunziante, Roma,
-- danielenunziante88@libero.it, CCBill subscription_id 0526309401000008597).
-- The payment was made through order 351, which belongs to the ADMIN account (user 464) -
-- DO NOT give the package to user 464. Pick the real customer's account first.
--
-- STEP 1 - find the customer's account and put its id below:
--   SELECT id, email, firstname, lastname, city FROM users
--   WHERE firstname LIKE '%Daniele%' OR lastname LIKE '%Nunziante%' OR email LIKE '%nunziante%';
--   SELECT id, name, type, credits, duration FROM packages WHERE id = 97;
-- If packages.type = 'credits', do NOT run the DELETE/INSERT on user_pack (credits only).
--
-- STEP 2 - set the id, run once, then delete this file.

SET @target_user = 0;  -- <-- REPLACE 0 with the customer's real users.id

UPDATE `orders`
SET `user_id` = @target_user, `status` = 'Accepted', `subscription_id` = '0526309401000008597', `updated_at` = NOW()
WHERE `id` = 351 AND `status` = 'Declined' AND @target_user > 0;

UPDATE `users`
SET `credits` = `credits` + (SELECT `credits` FROM `packages` WHERE `id` = 97)
WHERE `id` = @target_user AND @target_user > 0;

DELETE FROM `user_pack` WHERE `user_id` = @target_user AND @target_user > 0;

INSERT INTO `user_pack` (`user_id`, `pack_id`, `expiration_date`, `created_at`, `updated_at`)
SELECT @target_user, `id`, DATE_ADD(NOW(), INTERVAL `duration` DAY), NOW(), NOW()
FROM `packages` WHERE `id` = 97 AND @target_user > 0;
