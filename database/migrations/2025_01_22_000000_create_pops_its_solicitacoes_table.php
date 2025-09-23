<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopsItsSolicitacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pops_its_solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registro_id');
            $table->unsignedBigInteger('solicitante_id');
            $table->string('tipo_solicitacao'); // 'exclusao', etc.
            $table->text('justificativa');
            $table->enum('status', ['pendente', 'aprovada', 'reprovada'])->default('pendente');
            $table->unsignedBigInteger('aprovada_por')->nullable();
            $table->timestamp('aprovada_em')->nullable();
            $table->text('observacao_reprovacao')->nullable();
            $table->timestamps();

            $table->foreign('registro_id')->references('id')->on('pops_its_registros')->onDelete('cascade');
            $table->foreign('solicitante_id')->references('id')->on('users');
            $table->foreign('aprovada_por')->references('id')->on('users');

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pops_its_solicitacoes');
    }
}
