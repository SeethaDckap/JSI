<?php
ini_set('memory_limit', '512M');

/**
 * DEV TOOL: reset customer password manually
 * 
 * DO NOT RELEASE TO PRODUCTION!
 * 
 * @author Epicor.ECC.Team
 * Curl/ fiddler/ HTTP requester URL @url
 * @url: http://ecc.magento2.dev/eccResponder.php
 */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

if (!empty($_POST)) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (!empty($email)) {
        $customerRegistry = $obj->get('Magento\Customer\Model\CustomerRegistry');
        $customerRepository = $obj->get('Magento\Customer\Model\ResourceModel\CustomerRepository');
        $encryptor = $obj->get('Magento\Framework\Encryption\Encryptor');
        $customer = $customerRepository->get($email);
        if ($customer->getId()) {
            $passwordHash = $encryptor->getHash($password, true);
            $customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken(null);
            $customerSecure->setRpTokenCreatedAt(null);
            $customerSecure->setPasswordHash($passwordHash);
            $customerRepository->save($customer, $passwordHash);
            $customerRegistry->remove($customer->getId());
            die('PASSWORD SET FOR ' . $customer->getEmail() . ' TO ' . $password);
        }
    }
}
?>
<form action="" method="POST">
    <p>
        <label for="email">Email Address</label>
        <input type="text" id="email" name="email" />
    </p>
    <p>
        <label for="email">Password</label>
        <input type="text" id="password" name="password" />
    </p>
    <p>
        <input type="submit" value="Update" />
    </p>
</form>
