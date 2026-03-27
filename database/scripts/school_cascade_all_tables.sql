ALTER TABLE schedules DROP FOREIGN KEY schedules_school_id_foreign;
ALTER TABLE schedules ADD CONSTRAINT schedules_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE schools_user DROP FOREIGN KEY schools_user_school_id_foreign;
ALTER TABLE schools_user ADD CONSTRAINT schools_user_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE schools_user DROP FOREIGN KEY schools_user_user_id_foreign;
ALTER TABLE schools_user ADD CONSTRAINT schools_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE setting_values DROP FOREIGN KEY setting_values_school_id_foreign;
ALTER TABLE setting_values ADD CONSTRAINT setting_values_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE training_groups DROP FOREIGN KEY training_groups_school_id_foreign;
ALTER TABLE training_groups ADD CONSTRAINT training_groups_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE assists DROP FOREIGN KEY assists_school_id_foreign;
ALTER TABLE assists ADD CONSTRAINT assists_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE assists DROP FOREIGN KEY assists_inscription_id_foreign;
ALTER TABLE assists ADD CONSTRAINT assists_inscription_id_foreign FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE competition_group_inscription DROP FOREIGN KEY competition_groups_inscriptions_competition_group_id_foreign;
ALTER TABLE competition_group_inscription ADD CONSTRAINT competition_groups_inscriptions_competition_group_id_foreign FOREIGN KEY (competition_group_id) REFERENCES competition_groups(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE competition_group_inscription DROP FOREIGN KEY competition_groups_inscriptions_inscription_id_foreign;
ALTER TABLE competition_group_inscription ADD CONSTRAINT competition_groups_inscriptions_inscription_id_foreign FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE competition_groups DROP FOREIGN KEY competition_groups_school_id_foreign;
ALTER TABLE competition_groups ADD CONSTRAINT competition_groups_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE competition_groups DROP FOREIGN KEY competition_groups_tournament_id_foreign;
ALTER TABLE competition_groups ADD CONSTRAINT competition_groups_tournament_id_foreign FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE competition_groups DROP FOREIGN KEY competition_groups_user_id_foreign;
ALTER TABLE competition_groups ADD CONSTRAINT competition_groups_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE contracts DROP FOREIGN KEY contracts_contract_types_FK;
ALTER TABLE contracts ADD CONSTRAINT contracts_contract_types_FK FOREIGN KEY (contract_type_id) REFERENCES contract_types(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE contracts ADD CONSTRAINT contracts_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE games DROP FOREIGN KEY matches_school_id_foreign;
ALTER TABLE games ADD CONSTRAINT matches_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE incidents MODIFY COLUMN school_id bigint unsigned DEFAULT 0 NOT NULL;
ALTER TABLE incidents ADD CONSTRAINT incidents_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE inscriptions DROP FOREIGN KEY inscriptions_school_id_foreign;
ALTER TABLE inscriptions ADD CONSTRAINT inscriptions_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE inscriptions DROP FOREIGN KEY inscriptions_player_id_foreign;
ALTER TABLE inscriptions ADD CONSTRAINT inscriptions_player_id_foreign FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE ON UPDATE RESTRICT;


ALTER TABLE invoices DROP FOREIGN KEY invoices_school_id_foreign;
ALTER TABLE invoices ADD CONSTRAINT invoices_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE payments DROP FOREIGN KEY payments_school_id_foreign;
ALTER TABLE payments ADD CONSTRAINT payments_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE payments DROP FOREIGN KEY payments_inscription_id_foreign;
ALTER TABLE payments ADD CONSTRAINT payments_inscription_id_foreign FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE payments DROP FOREIGN KEY payments_training_group_id_foreign;
ALTER TABLE payments ADD CONSTRAINT payments_training_group_id_foreign FOREIGN KEY (training_group_id) REFERENCES training_groups(id) ON DELETE CASCADE ON UPDATE RESTRICT;


ALTER TABLE payments_received DROP FOREIGN KEY payments_received_school_id_foreign;
ALTER TABLE payments_received ADD CONSTRAINT payments_received_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE players DROP FOREIGN KEY players_school_id_foreign;
ALTER TABLE players ADD CONSTRAINT players_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE profiles DROP FOREIGN KEY profiles_user_id_foreign;
ALTER TABLE profiles ADD CONSTRAINT profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE tournament_payouts DROP FOREIGN KEY tournament_payouts_school_id_foreign;
ALTER TABLE tournament_payouts ADD CONSTRAINT tournament_payouts_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE tournaments DROP FOREIGN KEY tournaments_school_id_foreign;
ALTER TABLE tournaments ADD CONSTRAINT tournaments_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE training_group_user DROP FOREIGN KEY training_group_user_training_group_id_foreign;
ALTER TABLE training_group_user ADD CONSTRAINT training_group_user_training_group_id_foreign FOREIGN KEY (training_group_id) REFERENCES training_groups(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE training_group_user DROP FOREIGN KEY training_group_user_user_id_foreign;
ALTER TABLE training_group_user ADD CONSTRAINT training_group_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE training_sessions DROP FOREIGN KEY training_sessions_school_id_foreign;
ALTER TABLE training_sessions ADD CONSTRAINT training_sessions_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE users MODIFY COLUMN school_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD CONSTRAINT users_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE peoples_players DROP FOREIGN KEY peoples_players_people_id_foreign;
ALTER TABLE peoples_players ADD CONSTRAINT peoples_players_people_id_foreign FOREIGN KEY (people_id) REFERENCES peoples(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE peoples_players DROP FOREIGN KEY peoples_players_player_id_foreign;
ALTER TABLE peoples_players ADD CONSTRAINT peoples_players_player_id_foreign FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE invoices DROP FOREIGN KEY invoices_inscription_id_foreign;
ALTER TABLE invoices ADD CONSTRAINT invoices_inscription_id_foreign FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE invoices DROP FOREIGN KEY invoices_training_group_id_foreign;
ALTER TABLE invoices ADD CONSTRAINT invoices_training_group_id_foreign FOREIGN KEY (training_group_id) REFERENCES training_groups(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE invoices DROP FOREIGN KEY invoices_created_by_foreign;
ALTER TABLE invoices ADD CONSTRAINT invoices_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE payments_received DROP FOREIGN KEY payments_received_invoice_id_foreign;
ALTER TABLE payments_received ADD CONSTRAINT payments_received_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE payments_received DROP FOREIGN KEY payments_received_created_by_foreign;
ALTER TABLE payments_received ADD CONSTRAINT payments_received_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE ON UPDATE RESTRICT;
