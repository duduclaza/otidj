/* ===== MIGRATION SIMPLES: Adicionar coluna notificacoes_ativadas ===== */
/* Execute esta versão simplificada no phpMyAdmin */

ALTER TABLE users 
ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
COMMENT '1 = Notificações ativadas, 0 = Notificações desativadas' 
AFTER status;
