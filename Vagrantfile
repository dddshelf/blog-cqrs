# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "puphpet/debian75-x64"
  config.vm.hostname = "webserver"
  config.vm.network :private_network, ip: "172.21.99.6"
  
  config.vm.synced_folder ".", "/var/www/cqrs-blog-engine", type: "nfs"
  
  config.vm.provider :virtualbox do |vb|
      vb.name = "CQRS Blog Engine"
      vb.customize ["modifyvm", :id, "--memory", "1024"]
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
      vb.customize ["modifyvm", :id, "--hpet", "on"]
  end
  
  config.vm.provision "shell", path: "resources/provisioning.sh"
end