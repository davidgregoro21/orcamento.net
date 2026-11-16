<?php
// Não existe mais tela de instalação: o banco é preparado sozinho no primeiro acesso ao site.
header('Location: ./', true, 301);
exit;
