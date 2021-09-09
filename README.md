# XPTO Monitor
==================
* Versão: 2.0
O XPTO Monitor, é uma interface Web para gerenciamento e abertura de chamados para incidentes(via e-mail) para os sites e servidores monitorados.

#Caracteristicas

Monitoramento de Serviços e Sites ( Via Ping ou HTTP);
Notificação por E-mail, telegram bot e Webhooks;
Gráfico com tempo de atividade e latência;
Dashboard de fácil entendimento com sinalizações em caso de indisponibilidade;
Abertura de Registro (e-mail) direto na DashBoard 

#Requisitos:
---------

* Web server
* MySQL database
* For PHP5: 5.5.9+
* For PHP7: 7.0.8+
* PHP Extensions (modules)

  * ext-curl
  * ext-ctype
  * ext-filter
  * ext-hash
  * ext-json
  * ext-libxml
  * ext-openssl
  * ext-pdo
  * ext-pcre
  * ext-sockets
  * ext-xml

Obs: Ao realizar a instalação em seu servidor linux, acesse os diretórios do sistema e execute o seguinte comando: php composer.phar install.

#Teste:
---------

Você pode testar a ferramenta através da Url: http://xptomonitor.com.br

* Usuário: usuario
* Senha: senha



#Licença:
---------


O XPTO Monitor é baseado no sistema @PHPServerMonitor, com algumas customs personalizadas para atender a algumas demandas e resolver problemas não supridas pelo sistema de origem. Tanto o @PHPServerMonitor quanto o XPTO Monitor são softwares livres: você pode redistribuí-lo e / ou modificá-lo sob os termos da GNU General Public License conforme publicada pela Free Software Foundation, seja a versão 3 da Licença, ou (por sua opção) qualquer versão posterior.

Você deve ter recebido uma cópia da GNU General Public License junto com o PHP Server Monitor. Caso contrário, consulte https://www.gnu.org/licenses/ .
