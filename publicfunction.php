    public function saveConfigurable(array $productSap)
    {
        $this->arraySap = $productSap['n0ZecomVariantesV3Response']['TiArtDet']['item'] ?? 0;
        $this->sapArrayVar = $productSap['n0ZecomVariantesV3Response']['TiArtDet']['item'] ?? 0;

        if(isset($productSap['n0ZecomVariantesV3Response']['TiArtDet']['item']['Sku'])){
            $skuBase = [
                0 => $productSap['n0ZecomVariantesV3Response']['TiArtDet']['item']
            ];
        }
        array_walk($this->arraySap, function( &$value ) {
            $value = $value['Sku'];
        });

        if(!isset($this->arraySap)){
            $this->logger->info('No se puede acceder a los elementos del response');
            return 'No se puede acceder a los elementos del response';
        }

        foreach(array_unique($this->arraySap) as $item){
            $associatedProductIds = [];

            try{
                $arraySapVariants = $this->sapArrayVar;
                if(isset($this->sapArrayVar['Sku'])){
                    $arraySapVariants = [
                        0 => $this->sapArrayVar
                    ];
                }

                if(isset($arraySapVariants['SkuHijo'])){
                    $arraySapVariants = [
                        0 => $arraySapVariants
                    ];
                }

                foreach($arraySapVariants as $itemSon){
                    if(isset($itemSon['SkuHijo']) && !empty($itemSon['SkuHijo'])  && $itemSon['Sku'] == $item){
                        $name = $itemSon['DescSku'] ?? '';
                        $images = $itemSon['ImgSku'] ?? '';
                        $categories = $itemSon['Categoria'] ?? '';
                        $longDescription = $itemSon['DescLarga'] ?? '';
                        $model = $itemSon['model'] ?? '';
                        $priority = $itemSon['priority'] ?? '';
                        $color = empty($itemSon['Color']) && isset($itemSon['Color']);
                        $bedTypes = empty($itemSon['Tamanio']) && isset($itemSon['Tamanio']);
                        $side = empty($itemSon['Side']) && isset($itemSon['Side']);
                        $firmness = empty($itemSon['Firmeza']) && isset($itemSon['Firmeza']);
                        try{
                            if($itemSon['Sku'] == $item && $productChild = $this->getProductBySku($itemSon['SkuHijo'])){
                                $associatedProductIds[] = $productChild->getId();
                                $this->saveAttribute([
                                    [$itemSon['SkuHijo'], $itemSon['Color'], 'color'],
                                    [$itemSon['SkuHijo'], $itemSon['Tamanio'], 'bed_types'],
                                    [$itemSon['SkuHijo'], $itemSon['Side'], 'side'],
                                    [$itemSon['SkuHijo'], $itemSon['Firmeza'], 'firmness']
                                ]);
                            }
                        } catch( \Exception $e) {
                            $this->logger->info("Error en variantes del producto ".$itemSon['SkuHijo']." a causa de: ".$e->getMessage());
                        }
                    }
                }

                if (!$product = $this->getProductBySku($item.'BASE')) {
                    $product = $this->productFactory->create();
                }
                $product->setSku($item.'BASE');
                $product->setName($name);
                $product->setStoreId(0);
                $product->setStatus(1);
                $product->setVisibility(Visibility::VISIBILITY_BOTH);
                $product->setTypeId('configurable');
                $product->setWebsiteIds(array(1));
                $product->setAttributeSetId(4);
                $product->setDescription($longDescription);
                $product->setCustomAttribute('availability', '1');
                $product->setCustomAttribute('url_key', 'BASE'.$item);
                $product->setCustomAttribute('meta_title', 'BASE'.$item);
                $product->setCustomAttribute('meta_keyword', 'BASE'.$item);
                $product->setCustomAttribute('meta_description', 'BASE'.$item);
                $product->setCustomAttribute('model',$model);
                $product->setCustomAttribute('priority',$priority);
                if(isset($categories) && !empty($categories)){
                    $product->setCategoryIds(explode(',',$categories));
                }

                $product->setStockData([
                    'use_config_manage_stock' => 1,
                    'manage_stock' => 1,
                    'is_in_stock' => 1
                ]);
                $product->save();
                $product->setStoreId(1);
                $product->save();

                /** Evita que se dupliquen las imagenes en la actualizacion */
                if($productImg = $this->productModel->load($product->getId())){
                    $productImg->setMediaGalleryEntries([]);
                    $this->productRepository->save($productImg);
                }
                $variants = [
                    'color' => $color,
                    'bed_types' => $bedTypes,
                    'side' => $side,
                    'firmness' => $firmness                
                ]; 

                $this->setVariants($variants, $product, $associatedProductIds);
                $this->saveSimple->saveImages($images, $product);
                $this->logger->info('Configurable: '.$item);
            }catch(\Exception $e){
                $this->logger->info('Error: '.$e->getMessage());
            }
        }
        return 'Actualización de productos configurables exitosa!';
    }