require 'sidekiq'
require 'sidekiq-scheduler'
require 'rubygems'
require 'csv'

class PtMstrWorker
  include Sidekiq::Worker

  def perform
    params = Hash.new
    # csv_text = File.read('path to csv')
    # csv = CSV.parse(csv_text, :headers => true)
    data = Array.new
    CSV.foreach("/home/alexis/Documents/Universidad/Proyecto/apiQAD/pt_mstr.csv", { encoding: "UTF-8", headers: false, header_converters: :symbol, converters: :all}) do |row|
      puts row
      # puts 'hola'
    end
    # puts data
    #data = CSV.read("/home/alexis/Documents/Universidad/Proyecto/apiQAD/pt_mstr.csv", { encoding: "UTF-8", headers: false, header_converters: :symbol, converters: :all})

    # scs.each do |row|
    #   if Sc.where(:siv_id => row[:numsiv]).empty?
    #     params[:siv_id] = row[:numsiv]
    #     params[:qad_id] = row[:codigoqad]#row[:numqad]
    #     params[:rut] = row[:rutc]
    #     params[:client_name] = row[:cliente]
    #     params[:name] = row[:producto]
    #     params[:sku] = row[:sku]
    #     params[:umx] = row[:umx]
    #     params[:siv_code] = row[:codigoqad]
    #     params[:kind] = row[:tipoxx]
    #     params[:sub_kind] = row[:subtipoxx]
    #     params[:materials] = row[:materiales]
    #     params[:structure] = row[:estructurap]
    #     params[:fechacreacion] = row[:fechacrea]
    #     params[:fechaval] = row[:fechaval]
    #     Sc.create(params)
    #   end
    # end
  end
end
