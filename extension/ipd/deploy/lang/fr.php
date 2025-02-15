<?php
$lang->deploy->common           = 'Plan de déploiement';
$lang->deploy->create           = 'Créer un plan de déploiement';
$lang->deploy->view             = 'Détails du déploiement';
$lang->deploy->finish           = 'Termin';
$lang->deploy->finishAction     = 'Déploiement terminé';
$lang->deploy->edit             = 'Editer';
$lang->deploy->editAction       = 'Editer déploiement';
$lang->deploy->delete           = 'Supprimer';
$lang->deploy->deleteAction     = 'Supprimer déploiement';
$lang->deploy->deleted          = 'Deleted';
$lang->deploy->activate         = 'Activer';
$lang->deploy->activateAction   = 'Activer déploiement';
$lang->deploy->browse           = 'Deploiement';
$lang->deploy->browseAction     = 'Deploy List';
$lang->deploy->scope            = 'Scope déploiement';
$lang->deploy->manageScope      = "Champ d'application de la gestion";
$lang->deploy->cases            = 'CasTests';
$lang->deploy->notify           = 'Notifier';
$lang->deploy->casesAction      = 'Cas de déploiement';
$lang->deploy->linkCases        = 'Associer CasTest';
$lang->deploy->unlinkCase       = 'Dissocier CasTest';
$lang->deploy->steps            = 'Etape déploiement';
$lang->deploy->manageStep       = 'Étapes administratives';
$lang->deploy->finishStep       = 'Finir Etape';
$lang->deploy->activateStep     = 'Activer Etape';
$lang->deploy->assignTo         = 'Assigner ';
$lang->deploy->assignAction     = 'Assigner Etape';
$lang->deploy->editStep         = 'Editer Etape';
$lang->deploy->deleteStep       = 'Supprimer Etape';
$lang->deploy->viewStep         = 'Détails Etape';
$lang->deploy->batchUnlinkCases = 'Dissocier par lot';
$lang->deploy->createdDate      = 'Date de création';

$lang->deploy->name       = 'Nom du Plan';
$lang->deploy->desc       = 'Description';
$lang->deploy->members    = 'Membres';
$lang->deploy->hosts      = 'Serveurs';
$lang->deploy->service    = 'Service';
$lang->deploy->product    = $lang->productCommon;
$lang->deploy->release    = 'Release';
$lang->deploy->package    = 'Package URL';
$lang->deploy->begin      = "C'est parti";
$lang->deploy->end        = 'Fin';
$lang->deploy->status     = 'Statut';
$lang->deploy->owner      = 'Propriétaire';
$lang->deploy->stage      = 'Phase';
$lang->deploy->ditto      = 'Idem';
$lang->deploy->manageAB   = 'Gestion';
$lang->deploy->title      = 'Titre';
$lang->deploy->content    = 'Contenu';
$lang->deploy->assignedTo = 'Assign';
$lang->deploy->finishedBy = 'Fini par';
$lang->deploy->createdBy  = 'Créateur';
$lang->deploy->result     = 'Resultat';
$lang->deploy->updateHost = 'Mise jour Serveurs';
$lang->deploy->removeHost = 'Serveurs supprimer';
$lang->deploy->addHost    = 'Nouveau Serveur';
$lang->deploy->hadHost    = 'Hébergement';

$lang->deploy->lblBeginEnd = 'Période';
$lang->deploy->lblBasic    = 'Information de Base';
$lang->deploy->lblProduct  = 'Liens';
$lang->deploy->lblMonth    = 'Courant';
$lang->deploy->toggle      = 'Bascule';

$lang->deploy->monthFormat = 'M Y';

$lang->deploy->statusList['wait'] = 'En attente';
$lang->deploy->statusList['done'] = 'Fait';

$lang->deploy->dateList['today']     = "Aujourd'hui";
$lang->deploy->dateList['tomorrow']  = 'Demain';
$lang->deploy->dateList['thisweek']  = 'Cette semaine';
$lang->deploy->dateList['thismonth'] = 'Ce mois';
$lang->deploy->dateList['done']      = $lang->deploy->statusList['done'];

$lang->deploy->stageList['wait']    = 'Avant déploiement';
$lang->deploy->stageList['doing']   = 'En déploiement';
$lang->deploy->stageList['testing'] = "Tests d'acceptance";
$lang->deploy->stageList['done']    = 'Après déploiement';

$lang->deploy->resultList['']        = '';
$lang->deploy->resultList['success'] = 'Fait';
$lang->deploy->resultList['fail']    = 'Echec';

$lang->deploy->confirmDelete     = 'Voulez - vous supprimer ce déploiement?';
$lang->deploy->confirmDeleteStep = 'Voulez - vous supprimer cette étape?';
$lang->deploy->errorTime         = "L'heure de fin doit être supérieure à l'heure de début!";
$lang->deploy->errorStatusWait   = 'If the status is Waiting, the FinishedBy should be empty';
$lang->deploy->errorStatusDone   = "Finishby ne doit pas être vide si l'état est terminé";
$lang->deploy->errorOffline      = "L'hôte dans la suppression et l'ajout du service ne peut pas exister en même temps.";
$lang->deploy->resultNotEmpty    = 'Le résultat ne peut pas être vide';

$lang->deploystep = new stdClass();
$lang->deploystep->status       = $lang->deploy->status;
$lang->deploystep->assignedTo   = $lang->deploy->assignedTo;
$lang->deploystep->finishedBy   = $lang->deploy->finishedBy;
$lang->deploystep->finishedDate = 'Finished Date';
$lang->deploystep->begin        = $lang->deploy->begin;
$lang->deploystep->end          = $lang->deploy->end;
