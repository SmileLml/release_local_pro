<?php
public function bind()
{
    return $this->loadExtension('zentaobiz')->bind();
}

public function createUser()
{
    return $this->loadExtension('zentaobiz')->createUser();
}
