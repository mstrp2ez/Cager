var Player=function(){
	this.m_EntityID=-1;
	this.m_vVel=new Vec2d(0,0);
	this.m_x=0;
	this.m_y=0;
	this.m_vVec=new Vec2d(0,0);
	this.m_Src='';
	this.m_Anim=new Animation();
	this.m_Loaded=false;
	this.m_ReadBytes=0;
	this.m_LerpT=0;
	this.m_LerpSpeed=140; //complete a lerp in N milliseconds
	this.m_LerpTargets=[];
	this.m_DiffThreshold=20;
	this.m_PredictMoveSpeed=20;
	this.debug=0;
	this.m_Dead=false;
	this.m_StateBuffer=[];
	this.m_Children=[];
	
	var xThis=this;
	this.init=function(p_ID,p_Data){
		xThis.m_EntityID=p_ID;
		xThis.ParseData(p_Data);
		xThis.m_Loaded=true;
		En.post('assets.php',{t:'playeranim.ani'},function(e){
			var json=JSON.parse(e);
			var anim=xThis.m_Anim;
			anim.m_AnimationRate=json.rate;
			anim.SetAnimationData(json.a);
			
			xThis.m_Anim.Load(json.src,function(){
				
			});
			
		});
		Mouse.RegisterEventListener('click',xThis.Shoot);
	}
	this.Snapshot=function(p_Data){
		xThis.ParseData(p_Data);
	}
	this.ParseData=function(p_Data){
		//2 bytes integral part of posx float, 2 bytes fractional part. Same for posy
		var idx=0;
		var i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		var i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		var x=parseFloat(i0+'.'+f0)*s0;
		var y=parseFloat(i1+'.'+f1)*s1;
		
		var wasDead=xThis.m_Dead;
		xThis.m_Dead=(p_Data[idx++]);
		if(xThis.m_Dead){
			xThis.m_Anim.SetAnimation(2);
		}else if(wasDead){
			xThis.m_Anim.SetAnimation(0);
		}
		
		if(xThis.m_Loaded){
			if(xThis.m_EntityID==EntityMngr.m_PlayerID){
				if(Math.abs(xThis.m_x-x)>xThis.m_DiffThreshold||Math.abs(xThis.m_y-y)>xThis.m_DiffThreshold){
				//	console.log('Correction: Belived('+xThis.m_x+' '+xThis.m_y+') was ('+x+' '+y+')');
				 	xThis.m_x=x;
					xThis.m_y=y; 
					
					
					
					/* TileManager.WorldOffsetX=-((xThis.m_x)-(canvas.width/2));
					TileManager.WorldOffsetY=-((xThis.m_y)-(canvas.height/2)); */
				}
			}else{
				xThis.m_x=x;
				xThis.m_y=y;
			}
		}else{
			xThis.m_x=x;
			xThis.m_y=y;
			
			xThis.m_PredictMoveSpeed=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
			
			if(xThis.m_EntityID==EntityMngr.m_PlayerID){
				TileManager.WorldOffsetX=-((xThis.m_x)-(canvas.width/2));
				TileManager.WorldOffsetY=-((xThis.m_y)-(canvas.height/2));
			} 
		}

		xThis.m_ReadBytes=idx;
	}
	this.ReadBytes=function(){return xThis.m_ReadBytes;}
	this.SaveState=function(){
		xThis.m_StateBuffer.push({x:xThis.m_x,y:xThis.m_y});
	}
	this.Shoot=function(e){
		//var no=EntityMngr.NewLocalEntity(6);
		xThis.debug=0;
	}
	this.Render=function(p_ctx){
		if(p_ctx===undefined){return;}
		var canvas=document.getElementById('canvas');
		
		for(var idx in xThis.m_Children){  //Render children behind player
			var c=xThis.m_Children[idx];
			if(c.Render!==undefined){
				c.Render(p_ctx);
			}
		}
		
		p_ctx.fillStyle="#af4444";
		if(xThis.m_EntityID==EntityMngr.m_PlayerID){
			var dim=xThis.m_Anim.FrameDimensions();
			xThis.m_Anim.m_x=(canvas.width/2)+(dim.w/2);
			xThis.m_Anim.m_y=canvas.height/2;
		}else{
			xThis.m_Anim.m_x=xThis.m_x+TileManager.WorldOffsetX;//+canvas.width/2;
			xThis.m_Anim.m_y=xThis.m_y+TileManager.WorldOffsetY;//+canvas.height/2;
		}
		xThis.m_Anim.Render();
	}
	this.Update=function(p_Delta){
		xThis.m_Anim.Animate(p_Delta);
		/* if(xThis.m_LerpTargets.length<=0){ */
		if(xThis.m_EntityID==EntityMngr.m_PlayerID){
			if(xThis.m_Dead){return;}
			var pressed=false;
			var tmp={x:xThis.m_x,y:xThis.m_y};
			if(Keyboard.Pressed(37)){
				tmp.x-=xThis.m_PredictMoveSpeed;
				pressed=true;
			}
			if(Keyboard.Pressed(39)){
				tmp.x+=xThis.m_PredictMoveSpeed;
				pressed=true;
			}
			if(Keyboard.Pressed(38)){
				tmp.y-=xThis.m_PredictMoveSpeed;
				pressed=true;
			}
			if(Keyboard.Pressed(40)){
				tmp.y+=xThis.m_PredictMoveSpeed;
				pressed=true;
			}

			/* var tmp={x:xThis.m_x,y:xThis.m_y};
			tmp.x+=xThis.m_vVel.m_fX;
			tmp.y+=xThis.m_vVel.m_fY;
			
			xThis.m_vVel.MultS(0.89);
			if(xThis.m_vVel.Length()<0.2){
				xThis.m_vVel.Set(0,0);
			} */
			if(pressed){
				xThis.m_Anim.SetAnimation(1);
			}else{
				xThis.m_Anim.SetAnimation(0);
			}
			
			
			var dim=xThis.m_Anim.FrameDimensions();
			if(dim!==false){
				var isWall=TileManager.WallInArea(tmp.x,tmp.y,dim.w,dim.h);
				if(!isWall){
					xThis.m_x=tmp.x;
					xThis.m_y=tmp.y;
					
					//xThis.m_Anim.SetAnimation(1);
				} 
			}
		
			TileManager.WorldOffsetX=-((xThis.m_x)-(canvas.width/2));
			TileManager.WorldOffsetY=-((xThis.m_y)-(canvas.height/2));
		}
		
	//	xThis.m_Anim.SetAnimation(1);
		
		
	}
}
window.Player=Player;

var Projectile=function(){
	this.m_EntityID=-1;
	this.m_ReadBytes=0;
	this.m_vOrg=new Vec2d(0,0);
	this.m_vDst=new Vec2d(0,0);
	this.m_LerpT=0;
	this.m_LastUpdate=0;
	this.m_LerpSpeed=800;
	this.m_LightSource=false;
	this.m_LerpDone=false;
	
	var xThis=this;
	this.init=function(p_EntityID,p_Data){
		xThis.m_EntityID=p_EntityID;
		xThis.ParseData(p_Data);
	}
	this.ReadBytes=function(){return xThis.m_ReadBytes;}
	this.ParseData=function(p_Data){
		
		//ProjectileOrigin
		var idx=0;
		var i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		var i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		var x=parseFloat(i0+'.'+f0)*s0;
		var y=parseFloat(i1+'.'+f1)*s1;
		xThis.m_vOrg.Set(x,y);
		
		//Projectile end point (for rays)
		i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		x=parseFloat(i0+'.'+f0)*s0;
		y=parseFloat(i1+'.'+f1)*s1;
		xThis.m_vDst.Set(x,y);
		
	//	console.log(xThis.m_vOrg.m_fX,xThis.m_vOrg.m_fY);
		
		xThis.m_ReadBytes=idx;
	}
	this.Render=function(p_ctx){
		p_ctx.strokeStyle="#cc4444";
		p_ctx.lineWidth=4;
		var x0=xThis.m_vOrg.m_fX+TileManager.WorldOffsetX;
		var y0=xThis.m_vOrg.m_fY+TileManager.WorldOffsetY;
		var x1=xThis.m_vDst.m_fX+TileManager.WorldOffsetX;
		var y1=xThis.m_vDst.m_fY+TileManager.WorldOffsetY;
		
		p_ctx.beginPath();
			p_ctx.moveTo(x0,y0);
			p_ctx.lineTo(x1,y1);
		p_ctx.stroke();
	}
	this.Update=function(p_Delta){
		if(xThis.m_LerpDone){return;}
		xThis.m_LerpT+=p_Delta/xThis.m_LerpSpeed;
		if(xThis.m_LerpT>=1.0){
			xThis.m_LerpT=0;
			xThis.m_LerpDone=true;
			return;
		}
		
		var x0=xThis.m_vOrg.m_fX;
		var y0=xThis.m_vOrg.m_fY;
		
		var x1=xThis.m_vDst.m_fX;
		var y1=xThis.m_vDst.m_fY;
		
		x=(x0+(x1-x0)*xThis.m_LerpT);
		y=(y0+(y1-y0)*xThis.m_LerpT);
		xThis.m_vOrg.Set(x,y);
	}
	
}

var Torch=function(){
	
	this.m_EntityID=-1;
	this.m_Anim=new Animation();
	this.m_ReadBytes=0;
	this.m_State=1;
	this.m_x=0;
	this.m_y=0;
	this.m_LightSource=true;
	this.m_LightRadius=400;
	
	var xThis=this;
	this.init=function(p_EntityID,p_Data){
		xThis.m_EntityID=p_EntityID;
		xThis.ParseData(p_Data);
		En.post('assets.php',{t:'torchanim.ani'},function(e){
			var json=JSON.parse(e);
			var anim=xThis.m_Anim;
			anim.m_AnimationRate=json.rate;
			anim.SetAnimationData(json.a);
			
			xThis.m_Anim.Load(json.src,function(){
				
			});
			
		});
	}
	this.LightSource=function(){return xThis.m_LightSource;}
	this.Radius=function(){return xThis.m_LightRadius;}
	this.ReadBytes=function(){return xThis.m_ReadBytes;}
	this.ParseData=function(p_Data){
		var idx=0;
		xThis.m_State=p_Data[idx++];
		var i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		var i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		var x=parseFloat(i0+'.'+f0)*s0;
		var y=parseFloat(i1+'.'+f1)*s1;
		xThis.m_x=x;
		xThis.m_y=y;
		xThis.m_ReadBytes=idx;
	}
	this.Update=function(p_Delta){
		xThis.m_Anim.Animate(p_Delta);
	}
	this.Render=function(p_ctx){
		p_ctx.fillStyle="#ff0000";
		var x=xThis.m_x+TileManager.WorldOffsetX;
		var y=xThis.m_y+TileManager.WorldOffsetY;
		xThis.m_Anim.m_x=x;
		xThis.m_Anim.m_y=y;
		xThis.m_Anim.Render();
	}
}
window.Torch=Torch;

var Mine=function(){
	this.m_EntityID=-1;
	this.m_Anim=new Animation();
	this.m_ReadBytes=0;
	this.m_State=1;
	this.m_x=0;
	this.m_y=0;
	
	var xThis=this;
	this.init=function(p_EntityID,p_Data){
		xThis.m_EntityID=p_EntityID;
		xThis.ParseData(p_Data);
		En.post('assets.php',{t:'mine.ani'},function(e){
			var json=JSON.parse(e);
			var anim=xThis.m_Anim;
			anim.m_AnimationRate=json.rate;
			anim.SetAnimationData(json.a);
			
			xThis.m_Anim.Load(json.src,function(){
				
			});
			
		});
	}
	this.ReadBytes=function(){return xThis.m_ReadBytes;}
	this.ParseData=function(p_Data){
		var idx=0;
		xThis.m_State=p_Data[idx++];
		var i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		var i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		var x=parseFloat(i0+'.'+f0)*s0;
		var y=parseFloat(i1+'.'+f1)*s1;
		xThis.m_x=x;
		xThis.m_y=y;
		xThis.m_ReadBytes=idx;
	}
	this.Update=function(p_Delta){
		xThis.m_Anim.Animate(p_Delta);
	}
	this.Render=function(p_ctx){
		p_ctx.fillStyle="#ff0000";
		var x=xThis.m_x+TileManager.WorldOffsetX;
		var y=xThis.m_y+TileManager.WorldOffsetY;
		xThis.m_Anim.m_x=x;
		xThis.m_Anim.m_y=y;
		xThis.m_Anim.Render();
	}
}

var HealthPack=function(){
	this.m_EntityID=-1;
	this.m_Anim=new Animation();
	this.m_ReadBytes=0;
	this.m_State=1;
	this.m_x=0;
	this.m_y=0;
	this.m_Cooldown=false;
	
	var xThis=this;
	this.init=function(p_EntityID,p_Data){
		xThis.m_EntityID=p_EntityID;
		xThis.ParseData(p_Data);
		En.post('assets.php',{t:'healthpack.ani'},function(e){
			var json=JSON.parse(e);
			var anim=xThis.m_Anim;
			anim.m_AnimationRate=json.rate;
			anim.SetAnimationData(json.a);
			
			xThis.m_Anim.Load(json.src,function(){
				
			});
			
		});
	}
	this.Snapshot=function(p_Data){
		xThis.ParseData(p_Data);
	}
	this.ReadBytes=function(){return xThis.m_ReadBytes;}
	this.ParseData=function(p_Data){
		var idx=0;
		xThis.m_State=p_Data[idx++];
		var i0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f0=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s0=(i0&0x8000)?-1:1;
		i0=(i0&0x7FFF);

		var i1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var f1=((p_Data[idx++]<<8)|(p_Data[idx++]&0xff));
		var s1=(i1&0x8000)?-1:1;
		i1=(i1&0x7FFF);
		
		var x=parseFloat(i0+'.'+f0)*s0;
		var y=parseFloat(i1+'.'+f1)*s1;
		xThis.m_x=x;
		xThis.m_y=y;
		
		xThis.m_Cooldown=p_Data[idx++];
		
		xThis.m_ReadBytes=idx;
	}
	this.Update=function(p_Delta){
		xThis.m_Anim.Animate(p_Delta);
	}
	this.Render=function(p_ctx){
		if(xThis.m_Cooldown){return;}
		p_ctx.fillStyle="#ff0000";
		var x=xThis.m_x+TileManager.WorldOffsetX;
		var y=xThis.m_y+TileManager.WorldOffsetY;
		xThis.m_Anim.m_x=x;
		xThis.m_Anim.m_y=y;
		xThis.m_Anim.Render();
	}
}