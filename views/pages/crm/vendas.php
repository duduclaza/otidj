<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Vendas - CRM</h1>
            <p class="text-gray-600 mt-2">Gerencie todo o ciclo de vendas em um só lugar</p>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-green-600 via-emerald-600 to-teal-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative p-8 md:p-12">
            <div class="max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-4 py-2 bg-green-400 text-green-900 text-sm font-bold rounded-full animate-pulse"> TRIAL EM BREVE</span>
                    <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full"> CRM PREMIUM</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Gestão Completa de Vendas</h2>
                <p class="text-xl text-green-100 mb-6">Controle todo o processo de vendas, desde a primeira abordagem até o fechamento do negócio. Aumente sua taxa de conversão com ferramentas profissionais.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">360°</div>
                        <div class="text-green-100 text-sm">Visão do Cliente</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">+40%</div>
                        <div class="text-green-100 text-sm">Aumento Conversão</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">-60%</div>
                        <div class="text-green-100 text-sm">Tempo Fechamento</div>
                    </div>
                </div>
                <div class="flex items-center gap-6 mb-6">
                    <div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold text-white">R$ 900</span>
                            <span class="text-xl text-green-200">/mês</span>
                        </div>
                        <p class="text-green-200 text-sm mt-1">CRM Completo com todos os módulos</p>
                    </div>
                </div>
                <button disabled class="bg-white text-green-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl opacity-60 cursor-not-allowed"> Trial Em Breve</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-8 shadow-lg border-t-4 border-green-600">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Gestão de Oportunidades</h3>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-3"><span class="text-green-600 font-bold"></span><span>Pipeline visual com drag & drop</span></li>
                <li class="flex items-start gap-3"><span class="text-green-600 font-bold"></span><span>Previsão de vendas com IA</span></li>
                <li class="flex items-start gap-3"><span class="text-green-600 font-bold"></span><span>Histórico completo de interações</span></li>
                <li class="flex items-start gap-3"><span class="text-green-600 font-bold"></span><span>Propostas e contratos integrados</span></li>
                <li class="flex items-start gap-3"><span class="text-green-600 font-bold"></span><span>Aprovações multinível</span></li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-8 shadow-lg border-t-4 border-emerald-600">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Analytics de Vendas</h3>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-3"><span class="text-emerald-600 font-bold"></span><span>Dashboard em tempo real</span></li>
                <li class="flex items-start gap-3"><span class="text-emerald-600 font-bold"></span><span>Metas individuais e de equipe</span></li>
                <li class="flex items-start gap-3"><span class="text-emerald-600 font-bold"></span><span>Ranking de vendedores</span></li>
                <li class="flex items-start gap-3"><span class="text-emerald-600 font-bold"></span><span>Relatórios personalizados</span></li>
                <li class="flex items-start gap-3"><span class="text-emerald-600 font-bold"></span><span>Análise de win/loss</span></li>
            </ul>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-8 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4"> Quer aumentar suas vendas?</h3>
        <p class="text-xl text-green-100 mb-6 max-w-2xl mx-auto">Fale com nossos especialistas e descubra como o CRM pode transformar seus resultados!</p>
        <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-green-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> Falar com Especialista</a>
    </div>
</section>

