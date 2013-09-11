<?php 
class RayDiAopCacheBillingRay0000000042b222f7000000002cd4c499Aop extends Ray\Di\Aop\CacheBilling implements Ray\Aop\WeavedInterface
{
    private $rayAopIntercept = true;
    public $rayAopBind;
    public function chargeOrder()
    {
        if ($this->rayAopIntercept) {
            $this->rayAopIntercept = false;
            $interceptors = $this->rayAopBind[__FUNCTION__];
            $annotation = isset($this->rayAopBind->annotation[__FUNCTION__]) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
            $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
            return $invocation->proceed();
        }
        $this->rayAopIntercept = true;
        return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }
}