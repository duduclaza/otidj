<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Marketing - CRM</h1>
            <p class="text-gray-600 mt-2">Automatize campanhas e gere mais leads qualificados</p>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-orange-600 via-red-600 to-pink-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative p-8 md:p-12">
            <div class="max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-4 py-2 bg-orange-400 text-orange-900 text-sm font-bold rounded-full animate-pulse"> TRIAL EM BREVE</span>
                    <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full"> CRM PREMIUM</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Marketing Automation & Campaigns</h2>
                <p class="text-xl text-orange-100 mb-8">Crie, automatize e acompanhe campanhas de marketing multicanal. Gere mais leads, nutra relacionamentos e converta com inteligência.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-3xl mb-2"></div>
                        <h3 class="font-bold text-white mb-1">Email Marketing</h3>
                        <p class="text-orange-100 text-sm">Templates + Automação</p>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-3xl mb-2"></div>
                        <h3 class="font-bold text-white mb-1">WhatsApp Marketing</h3>
                        <p class="text-orange-100 text-sm">Campanhas em Massa</p>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-3xl mb-2"></div>
                        <h3 class="font-bold text-white mb-1">SMS Marketing</h3>
                        <p class="text-orange-100 text-sm">Alta Taxa Abertura</p>
                    </div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20 mb-8">
                    <h3 class="text-white font-bold mb-3 flex items-center gap-2"><span class="text-2xl"></span> Automação Inteligente</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <ul class="space-y-2 text-orange-100 text-sm">
                            <li> Fluxos de nutrição (Lead Nurturing)</li>
                            <li> Disparo automático por comportamento</li>
                            <li> Segmentação dinâmica</li>
                            <li> A/B Testing integrado</li>
                        </ul>
                        <ul class="space-y-2 text-orange-100 text-sm">
                            <li> Landing Pages otimizadas</li>
                            <li> Formulários inteligentes</li>
                            <li> Lead Scoring automático</li>
                            <li> Integração redes sociais</li>
                        </ul>
                    </div>
                </div>
                <div class="flex items-center gap-6 mb-6">
                    <div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold text-white">R$ 900</span>
                            <span class="text-xl text-orange-200">/mês</span>
                        </div>
                        <p class="text-orange-200 text-sm mt-1">CRM Completo com todos os módulos</p>
                    </div>
                </div>
                <button disabled class="bg-white text-orange-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl opacity-60 cursor-not-allowed"> Trial Em Breve</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-8 shadow-lg border-t-4 border-orange-600">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Analytics Completo</h3>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-3"><span class="text-orange-600 font-bold"></span><span>Taxa de abertura e cliques</span></li>
                <li class="flex items-start gap-3"><span class="text-orange-600 font-bold"></span><span>ROI por campanha</span></li>
                <li class="flex items-start gap-3"><span class="text-orange-600 font-bold"></span><span>Funil de conversão</span></li>
                <li class="flex items-start gap-3"><span class="text-orange-600 font-bold"></span><span>Heatmaps de cliques</span></li>
                <li class="flex items-start gap-3"><span class="text-orange-600 font-bold"></span><span>Relatórios customizados</span></li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-8 shadow-lg border-t-4 border-red-600">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Editor Visual</h3>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-3"><span class="text-red-600 font-bold"></span><span>Drag & drop editor</span></li>
                <li class="flex items-start gap-3"><span class="text-red-600 font-bold"></span><span>Templates profissionais</span></li>
                <li class="flex items-start gap-3"><span class="text-red-600 font-bold"></span><span>Biblioteca de imagens</span></li>
                <li class="flex items-start gap-3"><span class="text-red-600 font-bold"></span><span>Personalização avançada</span></li>
                <li class="flex items-start gap-3"><span class="text-red-600 font-bold"></span><span>Preview em tempo real</span></li>
            </ul>
        </div>
    </div>

    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl p-8 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4"> Potencialize seu Marketing!</h3>
        <p class="text-xl text-orange-100 mb-6 max-w-2xl mx-auto">Fale com nossa equipe e descubra como automatizar e escalar suas campanhas de marketing.</p>
        <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-orange-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> Solicitar Demonstração</a>
    </div>
</section>

