/* ===== MIGRATION: Adicionar campos de aprovação em amostragens_2 ===== */
/* Data: 09/10/2025 */
/* Descrição: Registra quem aprovou/reprovou a amostragem e quando */

ALTER TABLE amostragens_2 
ADD COLUMN aprovado_por INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que aprovou/reprovou' AFTER status_final,
ADD COLUMN aprovado_em DATETIME NULL DEFAULT NULL COMMENT 'Data e hora da aprovação/reprovação' AFTER aprovado_por,
ADD CONSTRAINT fk_amostragens_2_aprovado_por FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL;

/* Criar índice para melhorar performance */
CREATE INDEX idx_amostragens_2_aprovado_por ON amostragens_2(aprovado_por);
