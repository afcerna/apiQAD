require 'sidekiq'
require 'sidekiq-scheduler'
require 'rubygems'

class GeneratorWorker
  include Sidekiq::Worker

  def perform
    system "php genera_ov2.php"
    #puts('helloworld')
  end
end
