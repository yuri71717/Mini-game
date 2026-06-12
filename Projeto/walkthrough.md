# FOCO — Encontre o Ponto (Instruções XAMPP)

Todos os arquivos do projeto foram criados e configurados diretamente na sua pasta do XAMPP:
📂 **`C:\xampp\htdocs\Projeto`**

Isso significa que você já pode testar o site completo pelo Apache!

---

## 🚀 Como Testar no Navegador

1. Certifique-se de que o **Apache** está iniciado no painel do XAMPP.
2. Abra o seu navegador e acesse:
   👉 **[http://localhost/Projeto/index.php](http://localhost/Projeto/index.php)**
3. Para testar o login direto, use a credencial padrão:
   - **E-mail:** `agente@foco.com`
   - **Senha:** `foco123`
4. Para testar a criação de novas contas:
   - Clique em **CADASTRAR-SE**.
   - Preencha o formulário e crie a conta.
   - O sistema salvará a conta automaticamente em um banco de dados local simples (`usuarios.json` criado na própria pasta).
   - Depois, use o e-mail e senha cadastrados para fazer login!

---

## 📁 Arquivos na Pasta `C:\xampp\htdocs\Projeto`

* **index.php**: Tela de login com suporte a login pré-definido e login via usuários cadastrados no JSON.
* **cadastro.php**: Tela de cadastro totalmente funcional. Salva os novos usuários no arquivo local `usuarios.json`.
* **home.php**: Painel principal do jogo com o cronômetro da sessão.
* **instrucoes.php**: Instruções da missão.
* **jogar.php**: A arena do jogo. A imagem da cidade (`city.png`) é carregada aqui. O ponto correto está escondido no meio dos prédios. Clicar no ponto correto dispara o efeito de sonar e redireciona para a página de resultado. Clicar fora pisca vermelho indicando o erro.
* **result.php**: Página de sucesso que mostra o tempo que o agente levou para encontrar o ponto e o tempo restante da sessão.
* **session_check.php**: Valida se a sessão do agente expirou (3 minutos de limite).
* **logout.php**: Limpa a sessão e desloga o usuário.
* **city.png**: Imagem de fundo do jogo.
* **usuarios.json**: Arquivo gerado automaticamente ao cadastrar um usuário para servir como banco de dados rápido e sem necessidade de MySQL.
