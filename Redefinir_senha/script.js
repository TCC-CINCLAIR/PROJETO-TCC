/* processo de mostrar e ocultar a senha  */
    document.querySelectorAll('.alternar-senha').forEach(function (botao) {
      botao.addEventListener('click', function () {
        var targetId = botao.getAttribute('data-target');
        var input = document.getElementById(targetId);

        if (input.type === 'password') {
          input.type = 'text';
          botao.setAttribute('aria-label', 'Ocultar senha');
        } else {
          input.type = 'password';
          botao.setAttribute('aria-label', 'Mostrar senha');
        }
      });
    });

   /* processo que confere se os dois campos de senha são iguais  */
    document.getElementById('form-redefinicao').addEventListener('submit', function (e) {
      e.preventDefault(); // remover essa linha dps que conectar com o backend

      var novaSenha = document.getElementById('nova-senha').value;
      var confirmaSenha = document.getElementById('confirma-senha').value;

      if (novaSenha !== confirmaSenha) {
        alert('As senhas não coincidem. Verifique e tente novamente.');
        return;
      }

      if (novaSenha.length < 6) {
        alert('A senha deve ter no mínimo 6 caracteres.');
        return;
      }

      
      alert('Senha redefinida com sucesso! (simulação) ');
    });