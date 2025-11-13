<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>
<section class='space-y-6'>
    <div class='flex justify-between items-center'>
        <div>
            <h1 class='text-3xl font-bold text-gray-900'> CRM - Em Breve</h1>
            <p class='text-gray-600 mt-2'>Módulo CRM disponível em breve - Trial para testes</p>
        </div>
    </div>
    <div class='bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-2xl p-12 text-center shadow-2xl'>
        <div class='max-w-3xl mx-auto'>
            <div class='mb-6'>
                <span class='px-6 py-3 bg-yellow-400 text-yellow-900 text-lg font-bold rounded-full animate-pulse'>
                     MÓDULO EM DESENVOLVIMENTO
                </span>
            </div>
            <h2 class='text-4xl font-bold text-white mb-4'>
                CRM Completo para Sua Empresa
            </h2>
            <p class='text-xl text-purple-100 mb-8'>
                Sistema completo de gerenciamento de relacionamento com clientes. Trial disponível em breve!
            </p>
            <div class='flex items-baseline justify-center gap-3 mb-8'>
                <span class='text-6xl font-bold text-white'>R$ 800</span>
                <span class='text-2xl text-purple-200'>/mês</span>
            </div>
            <div class='flex justify-center gap-4'>
                <a href='mailto:comercial@sgqoti.com' class='bg-white text-purple-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all'>
                     Entrar em Contato
                </a>
            </div>
        </div>
    </div>
</section>
