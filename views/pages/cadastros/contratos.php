<?php
$title = 'Cadastro de Contratos - SGQ OTI';
?>

<section class="space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          📄 Cadastro de Contratos
        </h1>
        <p class="text-gray-600 mt-1">Gestão completa de contratos com clientes e fornecedores</p>
      </div>
    </div>
  </div>

  <!-- Em Breve -->
  <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-sm border-2 border-blue-200 p-12">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      
      <h2 class="text-3xl font-bold text-gray-900 mb-4">Em Breve! 🚀</h2>
      <p class="text-xl text-gray-600 mb-6">Estamos desenvolvendo este módulo</p>
      
      <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg p-6 shadow-sm">
          <h3 class="font-semibold text-gray-900 mb-4">📋 Funcionalidades Planejadas:</h3>
          <ul class="text-left space-y-3 text-gray-700">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Cadastro completo de contratos com clientes e fornecedores</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Controle de vigência, renovações e aditivos</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Alertas automáticos de vencimento</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Upload de documentos e anexos</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Dashboard com status e indicadores</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Relatórios e histórico de contratos</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="mt-8">
        <div class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold">
          <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Em desenvolvimento
        </div>
      </div>
    </div>
  </div>
</section>

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
