INSERT IGNORE INTO roles (id, role_name) VALUES (1, 'SUPER_ADMIN'), (2, 'ADMIN'), (3, 'OPERATOR'), (4, 'EDITOR'), (5, 'GURU');
INSERT IGNORE INTO users (id, username, email, password_hash, role_id, status) VALUES ('user-uuid-superadmin', 'superadmin', 'admin@sdnbarambai.sch.id', '\\\.n8D807csWj.962ZO13S7d4PjGZ1K7D7/96Rpoz.7PZ5S3kq', 1, 'ACTIVE'); -- password is 'admin123'
